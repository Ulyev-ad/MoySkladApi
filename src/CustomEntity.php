<?php


namespace Dobro\MoySkladApi;


class CustomEntity extends MoySklad
{
    protected $path = "entity/customentity";

    public function createValue($value, $id)
    {
        $result = $this->builderQuery($this->path . "/" . $id, "POST", $value, ["Content-Type" => "application/json"]);
        return (object)$result;
    }

    public function updateValue($value, $id_entity, $id_value)
    {
        $result = $this->builderQuery($this->path . "/" . $id_entity . '/' . $id_value, "PUT", $value, ["Content-Type" => "application/json"]);
        return (object)$result;
    }

    public function deleteValue($id_entity, $id_value)
    {
        $result = $this->builderQuery($this->path . "/" . $id_entity . '/' . $id_value, "DELETE", [], ["Content-Type" => "application/json"]);
        return $this;
    }

    public function getEntities($id)
    {
        $this->req->rows = $this->builderQuery($this->path . "/" . $id, "GET", [], ["Content-Type" => "application/json"]);
        return collect($this->req->rows);
    }

    public function getEntityVal($id_custom_field)
    {
        if ($this->id_query != false) {
            $id = $this->id_query;
        } else {
            $id = $this->req->rows{0}->id;
        }
        $this->req->rows = $this->builderQuery($this->path . "/" . $id_custom_field . '/' . $id, "GET", [], ["Content-Type" => "application/json"]);
        return $this->req->rows;
    }
}
