<?php

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/src/Ticket.php';
require __DIR__ . '/src/TicketFetcher.php';
require __DIR__ . '/src/CsvExporter.php';

$subdomain = 'nocompany-68451';
$email = 'd.tatochenko@gmail.com';
$apiToken = '62ZRFqYFFeOGvn7ZVYBEwLgBTy9kgf7bu2WoDvNm';

$fetcher = new TicketFetcher($subdomain, $email, $apiToken);
$tickets = $fetcher->fetchTickets();

CsvExporter::export(__DIR__ . '/src/exports/tickets.csv', $tickets);

echo "Tickets exported: " . count($tickets) . "\n";
