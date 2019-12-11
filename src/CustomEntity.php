<?php


namespace Dobro\MoySkladApi;


class CustomEntity extends MoySklad
{
    protected $path = "entity/customentity";

    public function createValue($value, $id)
    {
        $result = $this->builderQuery($this->path . "/" . $id, "POST", $value, ["Content-Type" => "application/json"]);
        return $this;
    }

    public function updateValue($value, $id_entity, $id_value)
    {
        $result = $this->builderQuery($this->path . "/" . $id_entity . '/'.$id_value, "PUT", $value, ["Content-Type" => "application/json"]);
        return $this;
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
}
