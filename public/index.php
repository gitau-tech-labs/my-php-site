<?php
// ============================================================
// NPCEA USSD + SMS Demo
// USSD: Africa's Talking endpoint. Data is hardcoded.
// SMS: fired after certain actions. Sandbox mode.
// ============================================================
header('Content-Type: text/plain');

// ============================================================
// CONFIG
// ============================================================
const AT_API_KEY  = 'atsk_c906f8ced77330fe13bd3156e357ae44d38a82d03ee0852fa0db479ccb360a24aac49f1f';
const AT_USERNAME = 'sandbox';                    // default sandbox username
const AT_SANDBOX  = true;
const AT_SENDER   = '';                           // blank = use default sender

// SAFETY LOCK: while true, every SMS goes to TEST_OVERRIDE instead of the
// real recipient. Prevents accidental costs during demos.
// Flip to false ONLY after rotating the API key and whitelisting numbers.
const DEMO_LOCK      = true;
const TEST_OVERRIDE  = '254745361106';            // your number

// ============================================================
// SMS SENDER
// ============================================================
function send_sms($to, $message)
{
    $digits = preg_replace('/\D/', '', $to);
    if (strlen($digits) === 10 && $digits[0] === '0') {
        $digits = '254' . substr($digits, 1);
    } elseif (strlen($digits) === 9) {
        $digits = '254' . $digits;
    }
    $to = '+' . $digits;

    $endpoint = AT_SANDBOX
        ? 'https://api.sandbox.africastalking.com/version1/messaging'
        : 'https://api.africastalking.com/version1/messaging';

    $payload = [
        'username' => AT_USERNAME,
        'to'       => $to,
        'message'  => $message,
    ];
    if (AT_SENDER !== '') {
        $payload['from'] = AT_SENDER;
    }

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_HTTPHEADER     => [
            'apiKey: ' . AT_API_KEY,
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    error_log(sprintf(
        '[SMS] to=%s http=%d err=%s resp=%s',
        $to, $httpCode, $curlErr, substr((string) $response, 0, 300)
    ));

    return $httpCode === 200 || $httpCode === 201;
}

// Route SMS through TEST_OVERRIDE when the demo lock is on
function sms_recipient($phone)
{
    return DEMO_LOCK ? TEST_OVERRIDE : $phone;
}

// ============================================================
// AFRICA'S TALKING REQUEST
// ============================================================
$sessionId   = $_POST['sessionId']   ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$text        = $_POST['text']        ?? '';

$phone = preg_replace('/\D/', '', $phoneNumber);
$input = $text === '' ? [] : explode('*', $text);

// ============================================================
// HARDCODED OFFICER "DATABASE"
// ============================================================
$officers = [
    '267181' => [
        'service_number' => '267181',
        'rank'           => 'Constable',
        'name'           => 'Isaac Gitau',
        'phone'          => '254745361106',
        'faculty'        => 'Criminal Investigation Faculty',
        'department'     => 'ICT Department',
        'leave_balance'  => 22,
        'parade_today'   => 'Annual leave',
        'parade_remarks' => 'Marked by IP Kenneth Njari',

        'leave_requests' => [
            ['type' => 'Annual', 'from' => '12 Aug 2026', 'to' => '16 Aug 2026', 'days' => 5, 'status' => 'Approved'],
            ['type' => 'Sick',   'from' => '03 Jul 2026', 'to' => '04 Jul 2026', 'days' => 2, 'status' => 'Approved'],
            ['type' => 'Annual', 'from' => '01 Oct 2026', 'to' => '05 Oct 2026', 'days' => 5, 'status' => 'Pending'],
        ],

        'notifications' => [
            ['title' => 'Leave approved',   'body' => '12-16 Aug annual leave approved.',  'when' => '10 Aug'],
            ['title' => 'Parade marked',    'body' => 'Marked Present on 25 Sep by Cmdr.', 'when' => 'Yesterday'],
            ['title' => 'Password changed', 'body' => 'Your password was updated.',        'when' => '2 days ago'],
        ],
    ],

    '267182' => [
        'service_number' => '267182',
        'rank'           => 'Corporal',
        'name'           => 'Mary Njeri Wanjiku',
        'phone'          => '254798765432',
        'faculty'        => 'Bravo Faculty',
        'department'     => 'Communications',
        'leave_balance'  => 18,
        'parade_today'   => 'Sick',
        'parade_remarks' => 'Medical certificate pending',

        'leave_requests' => [
            ['type' => 'Sick',   'from' => '26 Sep 2026', 'to' => '27 Sep 2026', 'days' => 2, 'status' => 'Pending'],
            ['type' => 'Annual', 'from' => '05 Jun 2026', 'to' => '09 Jun 2026', 'days' => 5, 'status' => 'Approved'],
        ],

        'notifications' => [
            ['title' => 'Sick leave logged', 'body' => 'Marked Sick today.',          'when' => 'Today'],
            ['title' => 'Welcome',           'body' => 'Welcome to the USSD portal.', 'when' => '3 days ago'],
        ],
    ],

    '267183' => [
        'service_number' => '267183',
        'rank'           => 'Inspector',
        'name'           => 'Peter Ochieng Odhiambo',
        'phone'          => '254701111111',
        'faculty'        => 'Alpha Faculty',
        'department'     => 'Investigations',
        'leave_balance'  => 30,
        'parade_today'   => 'Duty Else where',
        'parade_remarks' => 'Attached to HQ Nairobi',

        'leave_requests' => [
            ['type' => 'Annual', 'from' => '20 Jul 2026', 'to' => '24 Jul 2026', 'days' => 5, 'status' => 'Approved'],
        ],

        'notifications' => [
            ['title' => 'Attachment notice', 'body' => 'Attached to HQ Nairobi.', 'when' => '2 days ago'],
        ],
    ],
];

$incident_types = [
    '1' => 'Traffic accident',
    '2' => 'Theft',
    '3' => 'Assault',
    '4' => 'Suspicious activity',
    '5' => 'Other',
];

$supervisor_ranks = [
    'Inspector', 'Chief Inspector', 'Assistant Superintendent',
    'Superintendent', 'Senior Superintendent', 'Commissioner of Police',
];

// ============================================================
// MENU STATE MACHINE
// ============================================================

// ---------- LEVEL 0: first dial ----------
if ($text === '') {
    echo "CON Welcome to NPCEA\n";
    echo "Faculty Records System\n";
    echo "----------------\n";
    echo "Enter your Service Number:";
    exit;
}

// ---------- LEVEL 1: user typed service number ----------
if (count($input) === 1) {
    $svc = trim($input[0]);

    if (!preg_match('/^\d{4,10}$/', $svc)) {
        echo "CON Invalid service number.\n";
        echo "Enter digits only (e.g. 267181):";
        exit;
    }

    $officer = $officers[$svc] ?? null;

    if (!$officer) {
        echo "END Service number not found.\n";
        echo "Contact HQ on 020-XXX-XXXX.";
        exit;
    }

    if ($officer['phone'] !== $phone) {
        send_sms(
            sms_recipient($officer['phone']),
            "NPCEA SECURITY: Someone dialed USSD using service number {$officer['service_number']} from a different phone. If this wasn't you, contact HQ immediately."
        );

        echo "END Access denied.\n";
        echo "This service number is not\n";
        echo "linked to the number you dialed.\n";
        echo "Contact HQ to update your record.";
        exit;
    }

    $first_name = explode(' ', $officer['name'])[0];
    $display_name = $officer['rank'] . ' ' . $first_name;
    $new_notifs = count($officer['notifications'] ?? []);

    $response  = "CON Welcome, {$display_name}\n";
    $response .= "Svc No: {$officer['service_number']}\n";
    $response .= "----------------\n";
    $response .= "1. My parade status\n";
    $response .= "2. My leave balance\n";
    $response .= "3. Apply for leave\n";
    $response .= "4. Report sick\n";
    $response .= "5. My posting details\n";
    $response .= "6. My leave requests\n";
    $response .= "7. Notifications ({$new_notifs} new)\n";
    $response .= "8. Report an incident\n";

    if (in_array($officer['rank'], $supervisor_ranks, true)) {
        $response .= "9. Mark parade\n";
        $response .= "10. Look up officer\n";
    }

    $response .= "0. Exit";
    echo $response;
    exit;
}

// ---------- LEVELS 2+ ----------
$svc     = trim($input[0]);
$officer = $officers[$svc] ?? null;

if (!$officer || $officer['phone'] !== $phone) {
    echo "END Session expired. Dial again.";
    exit;
}

$action        = array_slice($input, 1);
$is_supervisor = in_array($officer['rank'], $supervisor_ranks, true);

if (empty($action)) {
    echo "END Session error. Dial again.";
    exit;
}

// ============================================================
// ACTION LEVEL 1
// ============================================================
if (count($action) === 1) {

    switch ($action[0]) {

        case '1':
            echo "END Parade status - today\n";
            echo "----------------\n";
            echo "Status: {$officer['parade_today']}\n";
            echo "----------------\n";
            echo "{$officer['parade_remarks']}";
            exit;

        case '2':
            echo "END Leave balance\n";
            echo "----------------\n";
            echo "Remaining: {$officer['leave_balance']} days\n";
            echo "Entitlement: 30 days\n";
            echo "Used this year: " . (30 - $officer['leave_balance']) . " days";
            exit;

        case '3':
            echo "CON Apply for leave\n";
            echo "Select type:\n";
            echo "1. Annual\n";
            echo "2. Sick\n";
            echo "3. Study\n";
            echo "4. Compassionate\n";
            echo "0. Back";
            exit;

        case '4':
            echo "CON Report sick\n";
            echo "This will:\n";
            echo "- Mark you Sick today\n";
            echo "- Notify your commander\n";
            echo "----------------\n";
            echo "1. Confirm\n";
            echo "2. Cancel";
            exit;

        case '5':
            echo "END My posting details\n";
            echo "----------------\n";
            echo "Rank: {$officer['rank']}\n";
            echo "Name: {$officer['name']}\n";
            echo "Svc No: {$officer['service_number']}\n";
            echo "Faculty: {$officer['faculty']}\n";
            echo "Dept: {$officer['department']}";
            exit;

        case '6':
            $requests = $officer['leave_requests'] ?? [];
            if (empty($requests)) {
                echo "END You have no leave requests.";
                exit;
            }
            $response  = "END My leave requests\n";
            $response .= "----------------\n";
            $recent = array_slice(array_reverse($requests), 0, 3);
            foreach ($recent as $i => $r) {
                $response .= ($i + 1) . ". {$r['type']} ({$r['days']}d)\n";
                $response .= "   {$r['from']} - {$r['to']}\n";
                $response .= "   Status: {$r['status']}\n";
            }
            echo rtrim($response, "\n");
            exit;

        case '7':
            $notifs = $officer['notifications'] ?? [];
            if (empty($notifs)) {
                echo "END You have no notifications.";
                exit;
            }
            $response  = "END Notifications\n";
            $response .= "----------------\n";
            $recent = array_slice(array_reverse($notifs), 0, 3);
            foreach ($recent as $i => $n) {
                $response .= ($i + 1) . ". {$n['title']}\n";
                $response .= "   {$n['body']}\n";
                $response .= "   {$n['when']}\n";
            }
            echo rtrim($response, "\n");
            exit;

        case '8':
            echo "CON Report an incident\n";
            echo "Select category:\n";
            echo "1. Traffic accident\n";
            echo "2. Theft\n";
            echo "3. Assault\n";
            echo "4. Suspicious activity\n";
            echo "5. Other\n";
            echo "0. Back";
            exit;

        case '9':
            if (!$is_supervisor) {
                echo "END Access denied.\nSupervisors only.";
                exit;
            }
            echo "CON Mark parade\n";
            echo "Who is this for?\n";
            echo "1. Myself\n";
            echo "2. By service number\n";
            echo "0. Back";
            exit;

        case '10':
            if (!$is_supervisor) {
                echo "END Access denied.\nSupervisors only.";
                exit;
            }
            echo "CON Look up officer\n";
            echo "Enter service number:";
            exit;

        case '0':
            echo "END Thank you. Dial again anytime.";
            exit;

        default:
            echo "END Invalid option.\nDial again to retry.";
            exit;
    }
}

// ============================================================
// ACTION LEVEL 2
// ============================================================
if (count($action) === 2) {

    if ($action[0] === '3') {
        $types = ['1' => 'Annual', '2' => 'Sick', '3' => 'Study', '4' => 'Compassionate'];

        if ($action[1] === '0') {
            echo "END Leave request cancelled.";
            exit;
        }
        if (!isset($types[$action[1]])) {
            echo "END Invalid leave type.";
            exit;
        }

        echo "CON {$types[$action[1]]} leave\n";
        echo "Enter start date (YYYYMMDD):\n";
        echo "Example: 20261001";
        exit;
    }

    if ($action[0] === '4') {
        if ($action[1] === '1') {
            $officer_sms = "NPCEA: Sick reported. Your commander has been notified. Ref SICK-2026-0042.";

            $commander_phone = '254700000000';
            $commander_sms = "NPCEA: {$officer['rank']} {$officer['name']} ({$officer['service_number']}) reported Sick today. Faculty: {$officer['faculty']}.";

            send_sms(sms_recipient($officer['phone']), $officer_sms);
            send_sms(sms_recipient($commander_phone), $commander_sms);

            echo "END Sick reported\n";
            echo "----------------\n";
            echo "Your commander has been notified.\n";
            echo "Ref: SICK-2026-0042";
            exit;
        }
        echo "END Sick report cancelled.";
        exit;
    }

    if ($action[0] === '8') {
        if ($action[1] === '0') {
            echo "END Incident report cancelled.";
            exit;
        }
        if (!isset($incident_types[$action[1]])) {
            echo "END Invalid category.";
            exit;
        }
        echo "CON {$incident_types[$action[1]]}\n";
        echo "Enter brief description\n";
        echo "(max 100 chars):";
        exit;
    }

    if ($action[0] === '9' && $is_supervisor) {
        if ($action[1] === '0') {
            echo "END Cancelled.";
            exit;
        }
        if ($action[1] === '1') {
            echo "CON Mark yourself as:\n";
            echo "1. Present\n";
            echo "2. Absent\n";
            echo "3. Sick\n";
            echo "0. Back";
            exit;
        }
        if ($action[1] === '2') {
            echo "CON Enter service number\n";
            echo "to mark:";
            exit;
        }
        echo "END Invalid option.";
        exit;
    }
}

// ============================================================
// ACTION LEVEL 3
// ============================================================
if (count($action) === 3) {

    if ($action[0] === '3') {
        $start = $action[2];
        if (!preg_match('/^\d{8}$/', $start)) {
            echo "CON Invalid date format.\n";
            echo "Enter start date (YYYYMMDD):\n";
            echo "Example: 20261001";
            exit;
        }
        echo "CON Enter end date (YYYYMMDD):\n";
        echo "Example: 20261005";
        exit;
    }

    if ($action[0] === '9' && $action[1] === '1' && $is_supervisor) {
        if ($action[2] === '0') {
            echo "END Cancelled.";
            exit;
        }
        $statuses = ['1' => 'Present', '2' => 'Absent', '3' => 'Sick'];
        if (!isset($statuses[$action[2]])) {
            echo "END Invalid status.";
            exit;
        }

        $new_status = $statuses[$action[2]];
        $sms = "NPCEA: You have been marked {$new_status} for today's parade. Ref PRD-2026-0091.";
        send_sms(sms_recipient($officer['phone']), $sms);

        echo "END Parade marked\n";
        echo "----------------\n";
        echo "You are now: {$new_status}\n";
        echo "Ref: PRD-2026-0091";
        exit;
    }

    if ($action[0] === '10' && $is_supervisor) {
        $target_svc = trim($action[1]);
        $target = $officers[$target_svc] ?? null;

        if (!$target) {
            echo "END Officer not found.";
            exit;
        }

        echo "END Officer status\n";
        echo "----------------\n";
        echo "Rank: {$target['rank']}\n";
        echo "Name: {$target['name']}\n";
        echo "Svc No: {$target['service_number']}\n";
        echo "Faculty: {$target['faculty']}\n";
        echo "Today: {$target['parade_today']}";
        exit;
    }
}

// ============================================================
// ACTION LEVEL 4
// ============================================================
if (count($action) === 4) {

    if ($action[0] === '3') {
        $start = $action[2];
        $end   = $action[3];

        if (!preg_match('/^\d{8}$/', $end)) {
            echo "CON Invalid end date.\n";
            echo "Enter end date (YYYYMMDD):";
            exit;
        }
        if ($end < $start) {
            echo "CON End date must be after start date.\n";
            echo "Enter end date (YYYYMMDD):";
            exit;
        }

        $start_ts = strtotime($start);
        $end_ts   = strtotime($end);
        $days     = (int) (($end_ts - $start_ts) / 86400) + 1;

        $start_fmt = date('d M Y', $start_ts);
        $end_fmt   = date('d M Y', $end_ts);

        $types = ['1' => 'Annual', '2' => 'Sick', '3' => 'Study', '4' => 'Compassionate'];
        $type  = $types[$action[1]] ?? 'Annual';

        echo "CON Confirm leave request:\n";
        echo "----------------\n";
        echo "Type: {$type}\n";
        echo "From: {$start_fmt}\n";
        echo "To: {$end_fmt}\n";
        echo "Days: {$days}\n";
        echo "----------------\n";
        echo "1. Submit\n";
        echo "2. Cancel";
        exit;
    }

    if ($action[0] === '8') {
        $category = $incident_types[$action[1]] ?? 'Other';
        $desc     = trim($action[3]);

        if (strlen($desc) < 5) {
            echo "CON Description too short.\n";
            echo "Enter brief description:";
            exit;
        }

        echo "CON Confirm incident:\n";
        echo "----------------\n";
        echo "Type: {$category}\n";
        echo "Desc: {$desc}\n";
        echo "----------------\n";
        echo "1. Submit\n";
        echo "2. Cancel";
        exit;
    }

    if ($action[0] === '9' && $action[1] === '2' && $is_supervisor) {
        $target_svc = trim($action[2]);
        $status_key = $action[3];

        $target = $officers[$target_svc] ?? null;
        if (!$target) {
            echo "END Officer not found.";
            exit;
        }

        $statuses = ['1' => 'Present', '2' => 'Absent', '3' => 'Sick'];
        if (!isset($statuses[$status_key])) {
            echo "END Invalid status.";
            exit;
        }

        $new_status = $statuses[$status_key];
        $sms = "NPCEA: You have been marked {$new_status} for today's parade by {$officer['rank']} {$officer['name']}. Ref PRD-2026-0092.";
        send_sms(sms_recipient($target['phone']), $sms);

        echo "END Parade marked\n";
        echo "----------------\n";
        echo "Officer: {$target['name']}\n";
        echo "Status: {$new_status}\n";
        echo "Ref: PRD-2026-0092";
        exit;
    }
}

// ============================================================
// ACTION LEVEL 5 — final submissions (fire SMS here)
// ============================================================
if (count($action) === 5) {

    if ($action[0] === '3') {
        if ($action[4] === '1') {
            $types = ['1' => 'Annual', '2' => 'Sick', '3' => 'Study', '4' => 'Compassionate'];
            $type  = $types[$action[1]] ?? 'Annual';

            $start = $action[2];
            $end   = $action[3];
            $start_ts = strtotime($start);
            $end_ts   = strtotime($end);
            $days     = (int) (($end_ts - $start_ts) / 86400) + 1;
            $start_fmt = date('d M Y', $start_ts);
            $end_fmt   = date('d M Y', $end_ts);

            $officer_sms = "NPCEA: {$type} leave request submitted. From {$start_fmt} to {$end_fmt} ({$days} days). Ref LV-2026-0043. Awaiting commander approval.";
            send_sms(sms_recipient($officer['phone']), $officer_sms);

            $commander_phone = '254700000000';
            $commander_sms = "NPCEA: New {$type} leave request from {$officer['rank']} {$officer['name']} ({$officer['service_number']}). {$start_fmt} to {$end_fmt} ({$days} days). Ref LV-2026-0043. Review in portal.";
            send_sms(sms_recipient($commander_phone), $commander_sms);

            echo "END Leave request submitted\n";
            echo "----------------\n";
            echo "Type: {$type}\n";
            echo "Your commander will review shortly.\n";
            echo "Ref: LV-2026-0043";
            exit;
        }
        echo "END Leave request cancelled.";
        exit;
    }

    if ($action[0] === '8') {
        if ($action[4] === '1') {
            $category = $incident_types[$action[1]] ?? 'Other';
            $desc     = trim($action[3]);

            $officer_sms = "NPCEA: Incident reported ({$category}). Ref INC-2026-0137. HQ will contact you shortly.";
            send_sms(sms_recipient($officer['phone']), $officer_sms);

            $hq_phone = '254711000000';
            $hq_sms = "NPCEA INCIDENT: {$category}. Reported by {$officer['rank']} {$officer['name']} ({$officer['service_number']}). Desc: {$desc}. Ref INC-2026-0137.";
            send_sms(sms_recipient($hq_phone), $hq_sms);

            echo "END Incident reported\n";
            echo "----------------\n";
            echo "Type: {$category}\n";
            echo "HQ will contact you shortly.\n";
            echo "Ref: INC-2026-0137";
            exit;
        }
        echo "END Incident report cancelled.";
        exit;
    }
}

// ============================================================
// Fallback
// ============================================================
echo "END Something went wrong.\nPlease dial again.";
