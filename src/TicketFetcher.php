<?php

use GuzzleHttp\Client;

class TicketFetcher
{
    private $client;
    private $subdomain;
    private $apiToken;
    private $email;
    private $userCache = [];
    private $groupCache = [];
    private $organizationCache = [];

    public function __construct($subdomain, $email, $apiToken)
    {
        $this->subdomain = $subdomain;
        $this->email = $email;
        $this->apiToken = $apiToken;

        $this->client = new Client([
            'base_uri' => "https://{$subdomain}.zendesk.com/api/v2/",
            'auth' => [$email . '/token', $apiToken],
            'http_errors' => false
        ]);
    }

    private function getUser($id)
    {
        if (isset($this->userCache[$id])) {
            return $this->userCache[$id];
        }

        $response = $this->client->get("users/{$id}.json");
        $data = json_decode($response->getBody()->getContents(), true);

        $user = [
            'id' => isset($data['user']['id']) ? $data['user']['id'] : null,
            'name' => isset($data['user']['name']) ? $data['user']['name'] : '',
            'email' => isset($data['user']['email']) ? $data['user']['email'] : '',
            'organization_id' => isset($data['user']['organization_id']) ? $data['user']['organization_id'] : null
        ];

        $this->userCache[$id] = $user;
        return $user;
    }

    private function getGroup($id)
    {
        if (isset($this->groupCache[$id])) {
            return $this->groupCache[$id];
        }

        $response = $this->client->get("groups/{$id}.json");
        $data = json_decode($response->getBody()->getContents(), true);

        $group = [
            'id' => isset($data['group']['id']) ? $data['group']['id'] : null,
            'name' => isset($data['group']['name']) ? $data['group']['name'] : ''
        ];

        $this->groupCache[$id] = $group;
        return $group;
    }

    private function getOrganization($id)
    {
        if (isset($this->organizationCache[$id])) {
            return $this->organizationCache[$id];
        }

        $response = $this->client->get("organizations/{$id}.json");
        $data = json_decode($response->getBody()->getContents(), true);

        $org = [
            'id' => isset($data['organization']['id']) ? $data['organization']['id'] : null,
            'name' => isset($data['organization']['name']) ? $data['organization']['name'] : ''
        ];

        $this->organizationCache[$id] = $org;
        return $org;
    }

    public function fetchTickets($perPage = 100)
    {
        $tickets = [];
        $url = "tickets.json?per_page={$perPage}";

        while ($url) {
            $response = $this->client->get($url);
            $data = json_decode($response->getBody()->getContents(), true);

            foreach ($data['tickets'] as $t) {
                $ticket = new Ticket();
                $ticket->id = $t['id'];
                $ticket->description = isset($t['description']) ? $t['description'] : '';
                $ticket->status = isset($t['status']) ? $t['status'] : '';
                $ticket->priority = isset($t['priority']) ? $t['priority'] : '';

                if (isset($t['assignee_id'])) {
                    $agent = $this->getUser($t['assignee_id']);
                    $ticket->agentId = $agent['id'];
                    $ticket->agentName = $agent['name'];
                    $ticket->agentEmail = $agent['email'];
                }

                if (isset($t['requester_id'])) {
                    $contact = $this->getUser($t['requester_id']);
                    $ticket->contactId = $contact['id'];
                    $ticket->contactName = $contact['name'];
                    $ticket->contactEmail = $contact['email'];

                    if ($contact['organization_id']) {
                        $org = $this->getOrganization($contact['organization_id']);
                        $ticket->companyId = $org['id'];
                        $ticket->companyName = $org['name'];
                    }
                }

                if (isset($t['group_id'])) {
                    $group = $this->getGroup($t['group_id']);
                    $ticket->groupId = $group['id'];
                    $ticket->groupName = $group['name'];
                }

                $commentsResponse = $this->client->get("tickets/{$ticket->id}/comments.json");
                $commentsData = json_decode($commentsResponse->getBody()->getContents(), true);
                foreach ($commentsData['comments'] as $c) {
                    $ticket->comments[] = isset($c['body']) ? $c['body'] : '';
                }

                $tickets[] = $ticket;
            }

            $url = $data['next_page'];
        }

        return $tickets;
    }
}
