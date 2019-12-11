<?php

namespace Dobro\MoySkladApi;

use Illuminate\Support\Collection;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use stdClass;

class MoySklad
{
    protected $req;
    private $Builder;

    public function __construct()
    {
        $this->req = new stdClass();
        $this->Builder = new Builder();
    }

    public function auth()
    {
        $login = "admin@programmer11";
        $password = "f34fbec0b20f";
        $credentials = base64_encode($login . ':' . $password);
        return $credentials;
    }

    protected function builderQuery($url, $method, $json = [], $head = [], $recursion = false)
    {
        return $this->Builder->builderQuery($url, $method, $this->req, $json, $head, $recursion);
    }

    /**
     * Подсчет вложенности в массиве
     *
     * @param array $arr
     * @param int $depth
     * @return Int
     */
    protected function getDepthArray(Array $arr, $depth = 0): Int
    {
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($arr));

        foreach ($it as $tmp) {
            $int = $it->getDepth();
            $depth >= $int ?: $depth = $int;
        }

        return $depth;
    }

    /**
     * @param $product_folder
     * @return $this
     */
    public function create($product_folder)
    {
        if (count($product_folder)) {
            $result = $this->builderQuery($this->path, "POST", $product_folder, ["Content-Type" => "application/json"]);
        }

        if (is_object($result) ){
            $this->req = new stdClass();
            dump($product_folder);
            dd($result);
            if ($this->getDepthArray($product_folder) && $this->getDepthArray((array)$result)) {
                for ($i = 0; $i < count($product_folder); $i++) {
                    $this->req->rows[$i] = (object)array_merge($product_folder[$i], (array)$result[$i]);
                }
            } else {
                $this->req->rows = (object)array_merge($product_folder, (array)$result);
            }
            return $this;
        }
    }

    /**
     * @param $product_folder
     * @return $this
     */
    public function update($product_folder)
    {
        $this->req->rows = array_merge($this->req->rows, $product_folder);
        $result = $this->builderQuery($this->path . "/" . $this->req->rows{0}->id, "PUT", $product_folder, ["Content-Type" => "application/json"]);
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function findById($id)
    {
        $this->req->rows = $this->builderQuery($this->path . "/" . $id, "GET", [], ["Content-Type" => "application/json"]);
        return collect($this->req->rows);
    }

    /**
     * @return $this
     */
    public function list()
    {
        $this->req = $this->builderQuery($this->path, "GET");
        return $this;
    }

    /**
     * @return mixed
     */
    public function first()
    {
        if (isset($this->req->rows) == false) {
            $this->list();
        }
        return collect($this->req->rows{0});
    }

    /**
     * @return mixed
     */
    public function shift()
    {
        $this->req->limit = 1;
        $this->list();
        return $this;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        if (isset($this->req->rows) == false) {
            $this->list();
        }
        return collect($this->req->rows);
    }

    /**
     * @param $key
     * @param $operator
     * @param $value
     * @return $this
     */
    public function where($key, $operator, $value)
    {
        if (!isset($this->req->filters)) {
            $this->req->filters = [];
        }

        $this->req->filters [] = [$key, $operator, $value];
        return $this;
    }

    /**
     * @param $field
     * @param string $sort
     * @return $this
     */
    public function orderBy($field, $sort = "asc")
    {
        if (!isset($this->req->orders)) {
            $this->req->orders = [];
        }

        $this->req->orders [] = [$field, $sort];
        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        $result = $this->builderQuery($this->path . "/" . $this->req->rows{0}->id, "DELETE", [], ["Content-Type" => "application/json"]);
        unset($this->req->rows{0});
        return $this;
    }

    public function limit($limit)
    {
        $this->req->limit = $limit;
    }

    public function offset($offset)
    {
        $this->req->offset = $offset;
    }


    /**
     * @return Collection
     */
    public function getAttributes()
    {
        if (isset($this->req->rows) == false) {
            $this->list();
        }
        return collect($this->req->rows);
    }

    public function listAttributes()
    {
        $this->req->rows = $this->builderQuery($this->path . "/metadata/attributes", "GET", [], ["Content-Type" => "application/json"]);
        return $this;
    }

    public function shiftAttribute()
    {
        $this->req->limit = 1;
        $this->listAttributes();
        return $this;
    }

    /**
     * @param $attributes
     * @return Collection
     */
    public function createAttribute($attributes)
    {
        $result = $this->builderQuery($this->path . "/metadata/attributes", "POST", $attributes, ["Content-Type" => "application/json"]);
        return collect($result);
    }

    /**
     * @return Collection
     */
    public function deleteAttribute()
    {
        $meta = [];
        foreach ($this->req->rows as $row) {
            $meta[] = $row->meta;
        }

        $result = $this->builderQuery($this->path . "/metadata/attributes", "POST", $meta, ["Content-Type" => "application/json"]);
        return collect($result);
    }

    public function findAttribute($id)
    {
        $result = $this->builderQuery($this->path . "/metadata/attributes/" . $id, "GET", [], ["Content-Type" => "application/json"]);
        return collect($result);
    }
}
