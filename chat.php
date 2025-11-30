<?php
// chat.php

// 1. Set your OpenAI API key here (or read it from environment/config)
$OPENAI_API_KEY = 'openai-domain-verification=dv-aHzU2kXvdc15DBrnAJkCuATi';  // replace this

// 2. Read JSON input from the browser
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = isset($input['message']) ? trim($input['message']) : '';

header('Content-Type: application/json');

if (!$userMessage) {
    echo json_encode(['error' => 'No message provided']);
    exit;
}

// 3. Build the messages array (personality + user question)
$messages = [
    [
        'role' => 'system',
        'content' => 'You are “Ask Ramdas AI”, the official portfolio assistant for Senior UX/UI Designer Ramdas Ware. ' .
                     'You answer questions about his skills, experience, projects, and UX process in a concise, professional tone. ' .
                     'Speak in first person as Ramdas (e.g., "I led...", "I worked on..."). ' .
                     'If the user asks about unrelated topics, politely bring the conversation back to Ramdas’s work and experience.'
    ],
    [
        'role' => 'user',
        'content' => $userMessage
    ]
];

// 4. Prepare the OpenAI API request
$payload = [
    'model' => 'gpt-4.1-mini',   // or another chat model available to you
    'messages' => $messages
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $OPENAI_API_KEY
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

// 5. Send request to OpenAI
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    echo json_encode(['error' => 'Curl error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

$data = json_decode($response, true);

if ($httpcode !== 200 || !isset($data['choices'][0]['message']['content'])) {
    echo json_encode([
        'error' => 'OpenAI API error',
        'details' => $data
    ]);
    exit;
}

$reply = $data['choices'][0]['message']['content'];

// 6. Return JSON to the browser
echo json_encode(['reply' => $reply]);
