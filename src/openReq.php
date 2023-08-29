<?php
namespace TzbClient;

class OpenReq {
    /**
     * 请求头reqHeader
     */
    var $reqHeader;
    /**
     * 请求 reqBody
     */
    var $reqBody;

    /**
     * @return mixed
     */
    public function getReqHeader()
    {
        return $this->reqHeader;
    }

    /**
     * @param mixed $reqHeader
     */
    public function setReqHeader($reqHeader): void
    {
        $this->reqHeader = $reqHeader;
    }

    /**
     * @return mixed
     */
    public function getReqBody()
    {
        return $this->reqBody;
    }

    /**
     * @param mixed $reqBody
     */
    public function setReqBody($reqBody): void
    {
        $this->reqBody = $reqBody;
    }



}