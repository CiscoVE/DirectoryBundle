<?php

namespace CiscoSystems\DirectoryBundle\Model;

class Session
{
    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var integer
     */
    protected $time;

    /**
     * Get sessionId
     *
     * @return string
     */
    public function getSessionId() { return $this->sessionId; }

    /**
     * Set sessionId
     *
     * @param string $sessionId
     * @return \CiscoSystems\DirectoryBundle\Model\Session
     */
    public function setSessionId( $sessionId ) { $this->sessionId = $sessionId; return $this; }

    /**
     * Get sessionId
     *
     * @return string
     */
    public function getValue() { return $this->value; }

    /**
     * Set value
     *
     * @param string $value
     * @return \CiscoSystems\DirectoryBundle\Model\Session
     */
    public function setValue( $value ) { $this->value = $value; return $this; }

    /**
     * Get time
     *
     * @return integer
     */
    public function getTime() { return $this->time; }

    /**
     * Set time
     *
     * @param integer $time
     * @return \CiscoSystems\DirectoryBundle\Model\Session
     */
    public function setTime( $time ) { $this->time = $time; return $this; }
}
