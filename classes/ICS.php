<?php

/*
 * Needed format for date: Y-m-d H:i (2020-12-30 15:40)
 * */

class ICS
{
    var $data;
    var $name;

    public function __construct($start, $end, $name, $description, $location)
    {
        $this->name = $name;
        $uid = uniqid();
        $this->data = "BEGIN:VCALENDAR\nVERSION:2.0\nMETHOD:PUBLISH\nBEGIN:VEVENT\nDTSTART:" . date("Ymd\THis\Z", strtotime("-2 hours", strtotime($start))) . "\nDTEND:" . date("Ymd\THis\Z", strtotime("-2 hours", strtotime($end))) . "\nLOCATION:" . $location . "\nTRANSP:OPAQUE\nSEQUENCE:0\nUID:" . $uid . "\nDTSTAMP:" . date("Ymd\THis\Z") . "\nSUMMARY:" . $name . "\nDESCRIPTION:" . $description . "\nPRIORITY:1\nCLASS:PUBLIC\nBEGIN:VALARM\nTRIGGER:-PT10080M\nACTION:DISPLAY\nDESCRIPTION:Reminder\nEND:VALARM\nEND:VEVENT\nEND:VCALENDAR\n";
    }

    public function save()
    {
        file_put_contents($this->name . ".ics", $this->data);
    }

    public function show()
    {
        header("Content-type:text/calendar");
        header('Content-Disposition: attachment; filename="' . $this->name . '.ics"');
        Header('Content-Length: ' . strlen($this->data));
        Header('Connection: close');
        echo $this->data;
    }
}


