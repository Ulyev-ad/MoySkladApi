<?php


namespace Dobro\MoySkladApi;


use ErrorException;
use GuzzleHttp\Client;

class Builder
{
    public function auth()
    {
        $login = config("");
        $password = "f34fbec0b20f";
        $credentials = base64_encode($login . ':' . $password);
        return $credentials;
    }

    /**
     * @param $url
     * @param $method
     * @param array $json
     * @param array $head
     * @param bool $recursion
     * @return mixed
     * @throws ErrorException
     */
    public function builderQuery($url, $method, $request, $auth, $json = [], $head = [], $recursion = false)
    {
        $data['http_errors'] = false;
        $data["json"] = $json;

        $headers = $head;
        $headers["Authorization"] = "Basic " . $auth;

        $client = new Client([
            'headers' => $headers
        ]);

        $url_b = 'https://online.moysklad.ru/api/remap/1.2/' . $url;
        $url_b .= "?" . $this->setFilters($request);
        $url_b .= "&" . $this->setOrders($request);
        $url_b .= "&" . $this->setLimit($request);
        $url_b .= "&" . $this->setOffset($request);

        $response = $client->request($method, $url_b, $data);

        $data = json_decode($response->getBody()->getContents());
        if ($response->getStatusCode() >= 300) {
            dump($data);
            throw new ErrorException('MoySklad request error - ' . $response->getStatusCode().".", $response->getStatusCode());
        }

        return $data;
    }

    /**
     * @param $request
     * @return string
     */
    private function setFilters($request)
    {
        if (!isset($request->filters)) {
            return false;
        }
        $filters = [];
        foreach ($request->filters as $filter) {
            $filters[] = join("", $filter);
        }
        return "filter=" . join(";", $filters);
    }

    /**
     * @param $request
     * @return string
     */
    private function setOrders($request)
    {
        if (!isset($request->orders)) {
            return false;
        }

        $orders = [];
        foreach ($request->orders as $order) {
            $orders[] = join(",", $order);
        }
        return "order=" . join(";", $orders);
    }

    /**
     * @param $request
     * @return bool|string
     */
    private function setLimit($request)
    {
        if (!isset($request->limit)) {
            return false;
        } else {
            return "limit=" . $request->limit;
        }
    }

    /**
     * @param $request
     * @return bool|string
     */
    private function setOffset($request)
    {
        if (!isset($request->offset)) {
            return false;
        } else {
            return "offset=" . $request->offset;
        }
    }
}
