<?php

namespace Model\Document;

class Events extends \Model\Document\Base\Events
{
    protected $events = array();

    public function getEvents()
    {
        return $this->events;
    }

    public function clearEvents()
    {
        $this->events = array();
    }

    public function preInsert()
    {
        $this->events[] = 'preInsert';
    }

    public function postInsert()
    {
        $this->events[] = 'postInsert';
    }

    public function preUpdate()
    {
        $this->events[] = 'preUpdate';
    }

    public function postUpdate()
    {
        $this->events[] = 'postUpdate';
    }

    public function preSave()
    {
        $this->events[] = 'preSave';
    }

    public function postSave()
    {
        $this->events[] = 'postSave';
    }

    public function preDelete()
    {
        $this->events[] = 'preDelete';
    }

    public function postDelete()
    {
        $this->events[] = 'postDelete';
    }
}
