<?php
/**
 * config.php  — LOCAL DEVELOPMENT template
 *
 * ⚠️  NEVER commit this file with real credentials.
 * ⚠️  On production (cPanel) this file lives OUTSIDE public_html at:
 *       /home/rier5192/config.php
 *
 * Copy this file, fill in your values, and keep it out of version control
 * by adding   config.php   to your .gitignore.
 */
return [
    // --- Database ---

    // 'DB_HOST' => 'localhost',
    // 'DB_NAME' => 'rier5192_rielcode',
    // 'DB_USER' => 'rier5192_rielcode_user',
    // 'DB_PASS' => 'CAqpph]]SsdTkVjM',

    'DB_HOST' => 'localhost',
    'DB_NAME' => 'rielcode',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    
    // --- OpenAI (chatbot) ---
    // Get your key from: https://platform.openai.com/api-keys
    'OPENAI_API_KEY' => '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',   // <-- paste your OpenAI API key here (sk-...)
];