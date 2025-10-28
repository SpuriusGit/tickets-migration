<?php

class CsvExporter
{
    public static function export($filePath, $tickets)
    {
        $handle = fopen($filePath, 'w');

        fputcsv($handle, [
            'Ticket ID', 'Description', 'Status', 'Priority',
            'Agent ID', 'Agent Name', 'Agent Email',
            'Contact ID', 'Contact Name', 'Contact Email',
            'Group ID', 'Group Name', 'Company ID', 'Company Name',
            'Comments'
        ]);

        foreach ($tickets as $ticket) {
            fputcsv($handle, $ticket->toArray());
        }

        fclose($handle);
    }
}
