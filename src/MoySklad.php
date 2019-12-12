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
    private $id_query = false;
    private $login;
    private $password;

    public function __construct()
    {
        $this->req = new stdClass();
        $this->Builder = new Builder();
        $this->login = config("moy_sklad.login");
        $this->password = config("moy_sklad.password");
    }

    /**
     * @return string
     */
    protected function auth()
    {
        $credentials = base64_encode($this->login . ':' . $this->password);
        return $credentials;
    }

    /**
     * @param $url
     * @param $method
     * @param array $json
     * @param array $head
     * @param bool $recursion
     * @return mixed
     * @throws \ErrorException
     */
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
     * @param $login
     * @param $password
     * @return $this
     */
    public function setAuth($login, $password)
    {
        $this->login = $login;
        $this->password = $password;
        return $this;
    }

    /**
     * @param $product_folder
     * @return $this
     * @throws \ErrorException
     */
    public function create($product_folder)
    {
        if (count($product_folder)) {
            $result = $this->builderQuery($this->path, "POST", $product_folder, ["Content-Type" => "application/json"]);
        }

        if (is_object($result)) {
            if ($this->getDepthArray($product_folder) && $this->getDepthArray((array)$result)) {
                for ($i = 0; $i < count($product_folder); $i++) {
                    $this->req->rows[$i] = (object)array_merge($product_folder[$i], (array)$result[$i]);
                }
            } else {
                $this->req->rows = (object)array_merge($product_folder, (array)$result);
            }
            return collect($this->req->rows);
        }
    }

    /**
     * @param $product_folder
     * @return $this
     * @throws \ErrorException
     */
    public function update($product_folder)
    {
        if ($this->id_query != false) {
            $id = $this->id_query;
        } else {
            $id = $this->req->rows{0}->id;
        }

        $this->req->rows = array_merge($this->req->rows, $product_folder);
        $result = $this->builderQuery($this->path . "/" . $id, "PUT", $product_folder, ["Content-Type" => "application/json"]);
        return collect($this->req->rows);
    }

    /**
     * @param $id
     * @return $this
     * @throws \ErrorException
     */
    public function findById($id)
    {
        $this->req->rows = $this->builderQuery($this->path . "/" . $id, "GET", [], ["Content-Type" => "application/json"]);
        return collect($this->req->rows);
    }

    /**
     * @return $this
     * @throws \ErrorException
     */
    public function list()
    {
        $this->req = $this->builderQuery($this->path, "GET");
        return $this;
    }

    /**
     * @return mixed
     * @throws \ErrorException
     */
    public function first()
    {
        if (isset($this->req->rows) == false) {
            $this->list();
        }
        return collect($this->req->rows)->first();
    }

    /**
     * @return mixed
     * @throws \ErrorException
     */
    public function shift()
    {
        $this->req->limit = 1;
        $this->list();
        return $this;
    }

    /**
     * @return mixed
     * @throws \ErrorException
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

        if ($key == "id" && $operator == "=") {
            $this->id_query = $value;
            return $this;
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
     * @throws \ErrorException
     */
    public function delete()
    {
        if ($this->id_query != false) {
            $id = $this->id_query;
        } else {
            $id = $this->req->rows{0}->id;
        }
        $result = $this->builderQuery($this->path . "/" . $id, "DELETE", [], ["Content-Type" => "application/json"]);
        unset($this->req->rows{0});
        return $this;
    }

    /**
     * @param $limit
     */
    public function limit($limit)
    {
        $this->req->limit = $limit;
    }

    /**
     * @param $offset
     */
    public function offset($offset)
    {
        $this->req->offset = $offset;
    }


    /**
     * @return Collection
     * @throws \ErrorException
     */
    public function getAttributes()
    {
        if (isset($this->req->rows) == false) {
            $this->list();
        }
        return collect($this->req->rows);
    }

    /**
     * @return $this
     * @throws \ErrorException
     */
    public function listAttributes()
    {
        $this->req->rows = $this->builderQuery($this->path . "/metadata/attributes", "GET", [], ["Content-Type" => "application/json"]);
        return $this;
    }

    /**
     * @return $this
     * @throws \ErrorException
     */
    public function shiftAttribute()
    {
        $this->req->limit = 1;
        $this->listAttributes();
        return $this;
    }

    /**
     * @param $attributes
     * @return Collection
     * @throws \ErrorException
     */
    public function createAttribute($attributes)
    {
        $result = $this->builderQuery($this->path . "/metadata/attributes", "POST", $attributes, ["Content-Type" => "application/json"]);
        return collect($result);
    }

    /**
     * @return Collection
     * @throws ErrorException
     * @throws \ErrorException
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

    /**
     * @param $id
     * @return Collection
     * @throws \ErrorException
     */
    public function findAttribute($id)
    {
        $result = $this->builderQuery($this->path . "/metadata/attributes/" . $id, "GET", [], ["Content-Type" => "application/json"]);
        return collect($result);
    }
}
