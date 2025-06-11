<?php
/**
 * Command Line Interface for the application
 * 
 * Usage: php cli.php [command] [arguments...]
 */

// Bootstrap the application
require_once __DIR__ . '/bootstrap.php';

// Get command from arguments
$args = array_slice($_SERVER['argv'], 1);
$commandName = $args[0] ?? 'help';
$commandArgs = array_slice($args, 1);

// Map of available commands
$commands = [
    'cache' => \App\Commands\CacheCommand::class,
    // Add other commands here
];

// Display help if no command specified
if ($commandName === 'help' || empty($commandName)) {
    echo "Available commands:\n";
    foreach ($commands as $name => $class) {
        echo "  $name\n";
    }
    echo "\nUsage: php cli.php [command] [arguments...]\n";
    exit(0);
}

// Check if command exists
if (!isset($commands[$commandName])) {
    echo "Error: Command '$commandName' not found.\n";
    echo "Run 'php cli.php help' to see available commands.\n";
    exit(1);
}

// Create command instance
$commandClass = $commands[$commandName];
$command = new $commandClass();

// Execute command
try {
    $exitCode = $command->execute($commandArgs);
    exit($exitCode);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
