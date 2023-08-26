<?php
class OpenHeaderRequest
{
    var $tranDate; //交易日期 yyyyMMdd
    var $tranTime; //交易时间
    var $cutType; //
    var $tranSeq; // 请求流水
    var $terminalNo;

    function __construct($requestId, $terminalNo)
    {
        $this->tranDate = date("Y-m-d");
        $this->tranTime = date("H:i:s.v");
        $this->cutType = "WEB";
        $this->tranSeq = $requestId;
        $this->terminalNo = $terminalNo;
    }

    /**
     * @return mixed
     */
    public function getTranDate()
    {
        return $this->tranDate;
    }

    /**
     * @param mixed $tranDate
     */
    public function setTranDate($tranDate): void
    {
        $this->tranDate = $tranDate;
    }

    /**
     * @return mixed
     */
    public function getTranTime()
    {
        return $this->tranTime;
    }

    /**
     * @param mixed $tranTime
     */
    public function setTranTime($tranTime): void
    {
        $this->tranTime = $tranTime;
    }

    /**
     * @return mixed
     */
    public function getCutType()
    {
        return $this->cutType;
    }

    /**
     * @param mixed $cutType
     */
    public function setCutType($cutType): void
    {
        $this->cutType = $cutType;
    }

    /**
     * @return mixed
     */
    public function getTranSeq()
    {
        return $this->tranSeq;
    }

    /**
     * @param mixed $tranSeq
     */
    public function setTranSeq($tranSeq): void
    {
        $this->tranSeq = $tranSeq;
    }

    /**
     * @return mixed
     */
    public function getTerminalNo()
    {
        return $this->terminalNo;
    }

    /**
     * @param mixed $terminalNo
     */
    public function setTerminalNo($terminalNo): void
    {
        $this->terminalNo = $terminalNo;
    } //


}