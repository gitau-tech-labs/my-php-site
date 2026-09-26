<?php
// ============================================================
// NPCEA USSD Demo - Africa's Talking endpoint
// Fully hardcoded. No database. No sessions.
// ============================================================
header('Content-Type: text/plain');

// ---------- Africa's Talking request ----------
$sessionId   = $_POST['sessionId']   ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$text        = $_POST['text']        ?? '';

// Normalise the phone: +254745361106 -> 254745361106
$phone = preg_replace('/\D/', '', $phoneNumber);

// Split accumulated input on '*'
$input = $text === '' ? [] : explode('*', $text);

// ============================================================
// HARDCODED OFFICER "DATABASE"
// Keyed by service number. The phone must match the one on file.
// ============================================================
$officers = [
    '267181' => [
        'service_number' => '267181',
        'rank'           => 'DEV',
        'name'           => 'Gitau',
        'phone'          => '254745361106',
        'faculty'        => 'Criminal Investigation Faculty',
        'department'     => 'ICT Department',
        'leave_balance'  => 22,
        'parade_today'   => 'Annual leave',
        'parade_remarks' => 'Marked by IP Kenneth Njari',

        'leave_requests' => [
            ['type' => 'Annual',  'from' => '12 Aug 2026', 'to' => '16 Aug 2026', 'days' => 5, 'status' => 'Approved'],
            ['type' => 'Sick',    'from' => '03 Jul 2026', 'to' => '04 Jul 2026', 'days' => 2, 'status' => 'Approved'],
            ['type' => 'Annual',  'from' => '01 Oct 2026', 'to' => '05 Oct 2026', 'days' => 5, 'status' => 'Pending'],
        ],

        'notifications' => [
            ['title' => 'Leave approved',    'body' => '12-16 Aug annual leave approved.',  'when' => '10 Aug'],
            ['title' => 'Parade marked',     'body' => 'Marked Present on 25 Sep by Cmdr.', 'when' => 'Yesterday'],
            ['title' => 'Password changed',  'body' => 'Your password was updated.',        'when' => '2 days ago'],
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
            ['type' => 'Sick',    'from' => '26 Sep 2026', 'to' => '27 Sep 2026', 'days' => 2, 'status' => 'Pending'],
            ['type' => 'Annual',  'from' => '05 Jun 2026', 'to' => '09 Jun 2026', 'days' => 5, 'status' => 'Approved'],
        ],

        'notifications' => [
            ['title' => 'Sick leave logged', 'body' => 'Marked Sick today.',           'when' => 'Today'],
            ['title' => 'Welcome',           'body' => 'Welcome to the USSD portal.',  'when' => '3 days ago'],
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
            ['title' => 'Attachment notice', 'body' => 'Attached to HQ Nairobi.',    'when' => '2 days ago'],
        ],
    ],
];

// ---------- Incident catalogue ----------
$incident_types = [
    '1' => 'Traffic accident',
    '2' => 'Theft',
    '3' => 'Assault',
    '4' => 'Suspicious activity',
    '5' => 'Other',
];

// ---------- Supervisor ranks that get extra menu items ----------
$supervisor_ranks = ['Inspector', 'Chief Inspector', 'Assistant Superintendent',
                     'Superintendent', 'Senior Superintendent', 'Commissioner of Police'];

// ============================================================
// MENU STATE MACHINE
// ============================================================

// ---------- LEVEL 0: first dial ----------
if ($text === '') {
    $response  = "CON Welcome to NPCEA\n";
    $response .= "Faculty Records System\n";
    $response .= "----------------\n";
    $response .= "Enter your Service Number:";
    echo $response;
    exit;
}

// ---------- LEVEL 1: user typed a service number ----------
if (count($input) === 1) {
    $svc = trim($input[0]);

    if (!preg_match('/^\d{4,10}$/', $svc)) {
        $response  = "CON Invalid service number.\n";
        $response .= "Enter digits only (e.g. 267181):";
        echo $response;
        exit;
    }

    $officer = $officers[$svc] ?? null;

    if (!$officer) {
        $response  = "END Service number not found.\n";
        $response .= "Contact HQ on 020-XXX-XXXX.";
        echo $response;
        exit;
    }

    if ($officer['phone'] !== $phone) {
        $response  = "END Access denied.\n";
        $response .= "This service number is not\n";
        $response .= "linked to the number you dialed.\n";
        $response .= "Contact HQ to update your record.";
        echo $response;
        exit;
    }

    // Success — main menu
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

    // Extra items for supervisors
    if (in_array($officer['rank'], $supervisor_ranks, true)) {
        $response .= "9. Mark parade\n";
        $response .= "10. Look up officer\n";
    }

    $response .= "0. Exit";
    echo $response;
    exit;
}

// ---------- LEVELS 2+: logged in ----------
$svc = trim($input[0]);
$officer = $officers[$svc] ?? null;

if (!$officer || $officer['phone'] !== $phone) {
    echo "END Session expired. Dial again.";
    exit;
}

$action = array_slice($input, 1);

if (empty($action)) {
    echo "END Session error. Dial again.";
    exit;
}

$is_supervisor = in_array($officer['rank'], $supervisor_ranks, true);

// ============================================================
// ACTION LEVEL 1 — main menu selections
// ============================================================
if (count($action) === 1) {

    switch ($action[0]) {

        case '1':
            $response  = "END Parade status - today\n";
            $response .= "----------------\n";
            $response .= "Status: {$officer['parade_today']}\n";
            $response .= "----------------\n";
            $response .= "{$officer['parade_remarks']}";
            echo $response;
            exit;

        case '2':
            $response  = "END Leave balance\n";
            $response .= "----------------\n";
            $response .= "Remaining: {$officer['leave_balance']} days\n";
            $response .= "Entitlement: 30 days\n";
            $response .= "Used this year: " . (30 - $officer['leave_balance']) . " days";
            echo $response;
            exit;

        case '3':
            $response  = "CON Apply for leave\n";
            $response .= "Select type:\n";
            $response .= "1. Annual\n";
            $response .= "2. Sick\n";
            $response .= "3. Study\n";
            $response .= "4. Compassionate\n";
            $response .= "0. Back";
            echo $response;
            exit;

        case '4':
            $response  = "CON Report sick\n";
            $response .= "This will:\n";
            $response .= "- Mark you Sick today\n";
            $response .= "- Notify your commander\n";
            $response .= "----------------\n";
            $response .= "1. Confirm\n";
            $response .= "2. Cancel";
            echo $response;
            exit;

        case '5':
            $response  = "END My posting details\n";
            $response .= "----------------\n";
            $response .= "Rank: {$officer['rank']}\n";
            $response .= "Name: {$officer['name']}\n";
            $response .= "Svc No: {$officer['service_number']}\n";
            $response .= "Faculty: {$officer['faculty']}\n";
            $response .= "Dept: {$officer['department']}";
            echo $response;
            exit;

        case '6':
            $requests = $officer['leave_requests'] ?? [];
            if (empty($requests)) {
                echo "END You have no leave requests.";
                exit;
            }

            $response  = "END My leave requests\n";
            $response .= "----------------\n";
            // Show most recent first, max 3
            $recent = array_slice(array_reverse($requests), 0, 3);
            foreach ($recent as $i => $r) {
                $response .= ($i + 1) . ". {$r['type']} ({$r['days']}d)\n";
                $response .= "   {$r['from']} - {$r['to']}\n";
                $response .= "   Status: {$r['status']}\n";
            }
            $response = rtrim($response, "\n");
            echo $response;
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
            $response = rtrim($response, "\n");
            echo $response;
            exit;

        case '8':
            $response  = "CON Report an incident\n";
            $response .= "Select category:\n";
            $response .= "1. Traffic accident\n";
            $response .= "2. Theft\n";
            $response .= "3. Assault\n";
            $response .= "4. Suspicious activity\n";
            $response .= "5. Other\n";
            $response .= "0. Back";
            echo $response;
            exit;

        case '9':
            if (!$is_supervisor) {
                echo "END Access denied.\nSupervisors only.";
                exit;
            }
            $response  = "CON Mark parade\n";
            $response .= "Who is this for?\n";
            $response .= "1. Myself\n";
            $response .= "2. By service number\n";
            $response .= "0. Back";
            echo $response;
            exit;

        case '10':
            if (!$is_supervisor) {
                echo "END Access denied.\nSupervisors only.";
                exit;
            }
            $response  = "CON Look up officer\n";
            $response .= "Enter service number:";
            echo $response;
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
// ACTION LEVEL 2 — sub-menu selections
// ============================================================
if (count($action) === 2) {

    // ---------- Leave flow: type selected ----------
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

        $response  = "CON {$types[$action[1]]} leave\n";
        $response .= "Enter start date (YYYYMMDD):\n";
        $response .= "Example: 20261001";
        echo $response;
        exit;
    }

    // ---------- Sick confirmation ----------
    if ($action[0] === '4') {
        if ($action[1] === '1') {
            $response  = "END Sick reported\n";
            $response .= "----------------\n";
            $response .= "Your commander has been notified.\n";
            $response .= "Ref: SICK-2026-0042";
            echo $response;
            exit;
        }
        echo "END Sick report cancelled.";
        exit;
    }

    // ---------- Incident flow: category selected ----------
    if ($action[0] === '8') {
        if ($action[1] === '0') {
            echo "END Incident report cancelled.";
            exit;
        }
        if (!isset($incident_types[$action[1]])) {
            echo "END Invalid category.";
            exit;
        }

        $response  = "CON {$incident_types[$action[1]]}\n";
        $response .= "Enter brief description\n";
        $response .= "(max 100 chars):";
        echo $response;
        exit;
    }

    // ---------- Mark parade: who? ----------
    if ($action[0] === '9' && $is_supervisor) {
        if ($action[1] === '0') {
            echo "END Cancelled.";
            exit;
        }
        if ($action[1] === '1') {
            // Marking self
            $response  = "CON Mark yourself as:\n";
            $response .= "1. Present\n";
            $response .= "2. Absent\n";
            $response .= "3. Sick\n";
            $response .= "0. Back";
            echo $response;
            exit;
        }
        if ($action[1] === '2') {
            $response  = "CON Enter service number\n";
            $response .= "to mark:";
            echo $response;
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

    // ---------- Leave: start date entered ----------
    if ($action[0] === '3') {
        $start = $action[2];
        if (!preg_match('/^\d{8}$/', $start)) {
            $response  = "CON Invalid date format.\n";
            $response .= "Enter start date (YYYYMMDD):\n";
            $response .= "Example: 20261001";
            echo $response;
            exit;
        }

        $response  = "CON Enter end date (YYYYMMDD):\n";
        $response .= "Example: 20261005";
        echo $response;
        exit;
    }

    // ---------- Mark parade self: status picked ----------
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

        $response  = "END Parade marked\n";
        $response .= "----------------\n";
        $response .= "You are now: {$statuses[$action[2]]}\n";
        $response .= "Ref: PRD-2026-0091";
        echo $response;
        exit;
    }

    // ---------- Look up officer: service number entered ----------
    if ($action[0] === '10' && $is_supervisor) {
        $target_svc = trim($action[1]);
        $target = $officers[$target_svc] ?? null;

        if (!$target) {
            echo "END Officer not found.";
            exit;
        }

        $response  = "END Officer status\n";
        $response .= "----------------\n";
        $response .= "Rank: {$target['rank']}\n";
        $response .= "Name: {$target['name']}\n";
        $response .= "Svc No: {$target['service_number']}\n";
        $response .= "Faculty: {$target['faculty']}\n";
        $response .= "Today: {$target['parade_today']}";
        echo $response;
        exit;
    }
}

// ============================================================
// ACTION LEVEL 4
// ============================================================
if (count($action) === 4) {

    // ---------- Leave: end date entered — confirmation ----------
    if ($action[0] === '3') {
        $start = $action[2];
        $end   = $action[3];

        if (!preg_match('/^\d{8}$/', $end)) {
            $response  = "CON Invalid end date.\n";
            $response .= "Enter end date (YYYYMMDD):";
            echo $response;
            exit;
        }

        if ($end < $start) {
            $response  = "CON End date must be after start date.\n";
            $response .= "Enter end date (YYYYMMDD):";
            echo $response;
            exit;
        }

        $start_ts = strtotime($start);
        $end_ts   = strtotime($end);
        $days     = (int) (($end_ts - $start_ts) / 86400) + 1;

        $start_fmt = date('d M Y', $start_ts);
        $end_fmt   = date('d M Y', $end_ts);

        $types = ['1' => 'Annual', '2' => 'Sick', '3' => 'Study', '4' => 'Compassionate'];
        $type  = $types[$action[1]] ?? 'Annual';

        $response  = "CON Confirm leave request:\n";
        $response .= "----------------\n";
        $response .= "Type: {$type}\n";
        $response .= "From: {$start_fmt}\n";
        $response .= "To: {$end_fmt}\n";
        $response .= "Days: {$days}\n";
        $response .= "----------------\n";
        $response .= "1. Submit\n";
        $response .= "2. Cancel";
        echo $response;
        exit;
    }

    // ---------- Incident: description entered — confirm ----------
    if ($action[0] === '8') {
        $category = $incident_types[$action[1]] ?? 'Other';
        $desc     = trim($action[3]);

        if (strlen($desc) < 5) {
            $response  = "CON Description too short.\n";
            $response .= "Enter brief description:";
            echo $response;
            exit;
        }

        $response  = "CON Confirm incident:\n";
        $response .= "----------------\n";
        $response .= "Type: {$category}\n";
        $response .= "Desc: {$desc}\n";
        $response .= "----------------\n";
        $response .= "1. Submit\n";
        $response .= "2. Cancel";
        echo $response;
        exit;
    }

    // ---------- Mark parade: officer picked, status picked ----------
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

        $response  = "END Parade marked\n";
        $response .= "----------------\n";
        $response .= "Officer: {$target['name']}\n";
        $response .= "Status: {$statuses[$status_key]}\n";
        $response .= "Ref: PRD-2026-0092";
        echo $response;
        exit;
    }
}

// ============================================================
// ACTION LEVEL 5 — leave submitted / incident submitted
// ============================================================
if (count($action) === 5) {

    // ---------- Leave submission ----------
    if ($action[0] === '3') {
        if ($action[4] === '1') {
            $types = ['1' => 'Annual', '2' => 'Sick', '3' => 'Study', '4' => 'Compassionate'];
            $type  = $types[$action[1]] ?? 'Annual';

            $response  = "END Leave request submitted\n";
            $response .= "----------------\n";
            $response .= "Type: {$type}\n";
            $response .= "Your commander will review shortly.\n";
            $response .= "Ref: LV-2026-0043";
            echo $response;
            exit;
        }
        echo "END Leave request cancelled.";
        exit;
    }

    // ---------- Incident submission ----------
    if ($action[0] === '8') {
        if ($action[4] === '1') {
            $category = $incident_types[$action[1]] ?? 'Other';

            $response  = "END Incident reported\n";
            $response .= "----------------\n";
            $response .= "Type: {$category}\n";
            $response .= "HQ will contact you shortly.\n";
            $response .= "Ref: INC-2026-0137";
            echo $response;
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
