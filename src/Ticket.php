<?php

class Ticket
{
    public $id;
    public $description;
    public $status;
    public $priority;

    public $agentId;
    public $agentName;
    public $agentEmail;

    public $contactId;
    public $contactName;
    public $contactEmail;

    public $groupId;
    public $groupName;

    public $companyId;
    public $companyName;

    public $comments = [];

    public function toArray()
    {
        return [
            $this->id,
            $this->description,
            $this->status,
            $this->priority,
            $this->agentId,
            $this->agentName,
            $this->agentEmail,
            $this->contactId,
            $this->contactName,
            $this->contactEmail,
            $this->groupId,
            $this->groupName,
            $this->companyId,
            $this->companyName,
            implode(" | ", $this->comments)
        ];
    }
}
