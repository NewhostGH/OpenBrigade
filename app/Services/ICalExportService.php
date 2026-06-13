<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Sabre\VObject\Component\VCalendar;

class ICalExportService
{
    /**
     * Build and stream an iCal (.ics) response.
     *
     * @param  string  $calName  Calendar display name (X-WR-CALNAME)
     * @param  array  $vevents  Each item: summary, location, description, uid,
     *                          dtstart (Carbon|string), dtend (Carbon|string), allDay (bool)
     * @param  string  $filename  Base filename without extension
     */
    public function toResponse(string $calName, array $vevents, string $filename): Response
    {
        $vcal = new VCalendar;
        $vcal->add('X-WR-CALNAME', $calName);
        $vcal->add('X-WR-TIMEZONE', 'Europe/Paris');

        foreach ($vevents as $v) {
            $props = [
                'SUMMARY' => $v['summary'],
                'LOCATION' => $v['location'] ?? '',
                'DESCRIPTION' => $v['description'] ?? '',
                'UID' => $v['uid'],
            ];

            if ($v['allDay'] ?? false) {
                $props['DTSTART;VALUE=DATE'] = Carbon::parse($v['dtstart'])->format('Ymd');
                $props['DTEND;VALUE=DATE'] = Carbon::parse($v['dtend'])->format('Ymd');
            } else {
                $props['DTSTART'] = Carbon::parse($v['dtstart'])->toDateTimeImmutable();
                $props['DTEND'] = Carbon::parse($v['dtend'])->toDateTimeImmutable();
            }

            $vcal->add('VEVENT', $props);
        }

        return response($vcal->serialize(), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Availabilitysition' => 'attachment; filename="'.$filename.'.ics"',
        ]);
    }
}
