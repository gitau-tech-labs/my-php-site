<?php
// ============================================================
// NPCEA USSD Demo - Africa's Talking endpoint
// All data is hardcoded for demonstration. No database.
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
        'rank'           => 'Constable',
        'name'           => 'Isaac Gitau',
        'phone'          => '254745361106',
        'faculty'        => 'Alpha Faculty',
        'department'     => 'Traffic',
        'leave_balance'  => 22,
        'parade_today'   => 'Present',
        'parade_remarks' => 'Marked by Cmdr. Otieno at 06:12',
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
    ],
];

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

    // Validate format
    if (!preg_match('/^\d{4,10}$/', $svc)) {
        $response  = "CON Invalid service number.\n";
        $response .= "Enter digits only (e.g. 267181):";
        echo $response;
        exit;
    }

    // Look up by service number
    $officer = $officers[$svc] ?? null;

    if (!$officer) {
        $response  = "END Service number not found.\n";
        $response .= "Contact HQ on 020-XXX-XXXX.";
        echo $response;
        exit;
    }

    // Check the phone matches the one on record
    if ($officer['phone'] !== $phone) {
        $response  = "END Access denied.\n";
        $response .= "This service number is not\n";
        $response .= "linked to the number you dialed.\n";
        $response .= "Contact HQ to update your record.";
        echo $response;
        exit;
    }

    // Success - show the main menu
    $display_name = $officer['rank'] . ' ' . explode(' ', $officer['name'])[0];

    $response  = "CON Welcome, {$display_name}\n";
    $response .= "Svc No: {$officer['service_number']}\n";
    $response .= "----------------\n";
    $response .= "1. My parade status\n";
    $response .= "2. My leave balance\n";
    $response .= "3. Apply for leave\n";
    $response .= "4. Report sick\n";
    $response .= "5. My posting details\n";
    $response .= "0. Exit";
    echo $response;
    exit;
}

// ---------- LEVELS 2+: logged in, show menu actions ----------
// At this point $input[0] is the service number. Everything after is menu action.

$svc = trim($input[0]);
$officer = $officers[$svc] ?? null;

// Guard: if we lost the officer (shouldn't happen mid-session)
if (!$officer || $officer['phone'] !== $phone) {
    echo "END Session expired. Dial again.";
    exit;
}

// Strip the service number to get the actual menu action path
$action = array_slice($input, 1);

// If the user hasn't picked an action yet (shouldn't happen, safety)
if (empty($action)) {
    echo "END Session error. Dial again.";
    exit;
}

// ---------- ACTION LEVEL 1 ----------
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

        case '0':
            echo "END Thank you. Dial again anytime.";
            exit;

        default:
            echo "END Invalid option.\nDial again to retry.";
            exit;
    }
}

// ---------- ACTION LEVEL 2 ----------
if (count($action) === 2) {

    // Leave sub-menu
    if ($action[0] === '3') {
        $types = [
            '1' => 'Annual',
            '2' => 'Sick',
            '3' => 'Study',
            '4' => 'Compassionate',
        ];

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

    // Report sick confirmation
    if ($action[0] === '4') {
        if ($action[1] === '1') {
            $response  = "END Sick reported\n";
            $response .= "----------------\n";
            $response .= "Your commander has been notified.\n";
            $response .= "Ref: SICK-2026-0042";
            echo $response;
            exit;
        } else {
            echo "END Sick report cancelled.";
            exit;
        }
    }
}

// ---------- ACTION LEVEL 3: leave start date entered ----------
if (count($action) === 3 && $action[0] === '3') {
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

// ---------- ACTION LEVEL 4: leave end date entered ----------
if (count($action) === 4 && $action[0] === '3') {
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

// ---------- ACTION LEVEL 5: leave submitted ----------
if (count($action) === 5 && $action[0] === '3') {
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
    } else {
        echo "END Leave request cancelled.";
        exit;
    }
}

// ---------- Fallback ----------
echo "END Something went wrong.\nPlease dial again.";
