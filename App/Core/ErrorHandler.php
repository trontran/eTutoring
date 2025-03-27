<?php
//
//namespace App\Core;
//
//use App\Models\ActivityTracker;
//
//class ErrorHandler
//{
//    /**
//     * Set up error and exception handlers
//     */
//    public static function init()
//    {
//        // Set error handler
//        set_error_handler([self::class, 'handleError']);
//
//        // Set exception handler
//        set_exception_handler([self::class, 'handleException']);
//
//        // Register shutdown function to catch fatal errors
//        register_shutdown_function([self::class, 'handleFatalError']);
//    }
//
//    /**
//     * Handle PHP errors
//     *
//     * @param int $errno Error level
//     * @param string $errstr Error message
//     * @param string $errfile File where error occurred
//     * @param int $errline Line number
//     * @return bool Whether the error was handled
//     */
//    public static function handleError($errno, $errstr, $errfile, $errline)
//    {
//        if (!(error_reporting() & $errno)) {
//            // This error code is not included in error_reporting
//            return false;
//        }
//
//        $errorType = self::getErrorType($errno);
//
//        // Format error message
//        $errorMessage = "[$errorType] $errstr in $errfile on line $errline";
//
//        // Log error to database
//        self::logError($errorType, $errorMessage);
//
//        return true;
//    }
//
//    /**
//     * Handle exceptions
//     *
//     * @param \Throwable $exception The exception
//     */
//    public static function handleException($exception)
//    {
//        $errorMessage = $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine();
//        $trace = $exception->getTraceAsString();
//
//        // Log exception to database
//        self::logError(get_class($exception), $errorMessage . "\nStack trace:\n" . $trace);
//
//        // Display error page
//        self::renderErrorPage('An application error has occurred', $errorMessage);
//
//        exit(1);
//    }
//
//    /**
//     * Handle fatal errors
//     */
//    public static function handleFatalError()
//    {
//        $error = error_get_last();
//
//        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
//            $errorType = self::getErrorType($error['type']);
//            $errorMessage = "[$errorType] {$error['message']} in {$error['file']} on line {$error['line']}";
//
//            // Log error to database
//            self::logError($errorType, $errorMessage);
//
//            // Display error page
//            self::renderErrorPage('A fatal error has occurred', $errorMessage);
//        }
//    }
//
//    /**
//     * Log error to database
//     *
//     * @param string $errorType Error type
//     * @param string $errorMessage Error message
//     */
//    private static function logError($errorType, $errorMessage)
//    {
//        try {
//            if (session_status() === PHP_SESSION_NONE) {
//                session_start();
//            }
//
//            $userId = isset($_SESSION['user']) ? $_SESSION['user']['user_id'] : null;
//            $url = isset($_GET['url']) ? $_GET['url'] : null;
//
//            $activityTracker = new ActivityTracker();
//            $activityTracker->trackError($errorType, $errorMessage, $userId, $url);
//        } catch (\Exception $e) {
//            // Fallback to error log if database logging fails
//            error_log("Error logging failed: " . $e->getMessage());
//            error_log("Original error: $errorType - $errorMessage");
//        }
//    }
//
//    /**
//     * Get error type name from error code
//     *
//     * @param int $errno Error code
//     * @return string Error type name
//     */
//    private static function getErrorType($errno)
//    {
//        switch ($errno) {
//            case E_ERROR:
//                return 'E_ERROR';
//            case E_WARNING:
//                return 'E_WARNING';
//            case E_PARSE:
//                return 'E_PARSE';
//            case E_NOTICE:
//                return 'E_NOTICE';
//            case E_CORE_ERROR:
//                return 'E_CORE_ERROR';
//            case E_CORE_WARNING:
//                return 'E_CORE_WARNING';
//            case E_COMPILE_ERROR:
//                return 'E_COMPILE_ERROR';
//            case E_COMPILE_WARNING:
//                return 'E_COMPILE_WARNING';
//            case E_USER_ERROR:
//                return 'E_USER_ERROR';
//            case E_USER_WARNING:
//                return 'E_USER_WARNING';
//            case E_USER_NOTICE:
//                return 'E_USER_NOTICE';
//            case E_STRICT:
//                return 'E_STRICT';
//            case E_RECOVERABLE_ERROR:
//                return 'E_RECOVERABLE_ERROR';
//            case E_DEPRECATED:
//                return 'E_DEPRECATED';
//            case E_USER_DEPRECATED:
//                return 'E_USER_DEPRECATED';
//            default:
//                return 'UNKNOWN';
//        }
//    }
//
//    /**
//     * Render an error page
//     *
//     * @param string $title Error title
//     * @param string $message Error message
//     */
//    private static function renderErrorPage($title, $message)
//    {
//        if (ob_get_level()) {
//            ob_end_clean();
//        }
//
//        // Only show detailed error messages in development environment
//        $displayDetails = false; // Set to true for development
//
//        if (!$displayDetails) {
//            $message = 'An error has occurred. Please try again later.';
//        }
//
//        echo '<!DOCTYPE html>
//            <html>
//            <head>
//                <title>Error</title>
//                <meta charset="UTF-8">
//                <meta name="viewport" content="width=device-width, initial-scale=1.0">
//                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
//            </head>
//            <body class="bg-light">
//                <div class="container py-5">
//                    <div class="row justify-content-center">
//                        <div class="col-md-8">
//                            <div class="card shadow-sm">
//                                <div class="card-header bg-danger text-white">
//                                    <h4 class="mb-0">' . htmlspecialchars($title) . '</h4>
//                                </div>
//                                <div class="card-body">
//                                    <p>' . htmlspecialchars($message) . '</p>
//                                    <a href="?url=home/index" class="btn btn-primary">Go Home</a>
//                                </div>
//                            </div>
//                        </div>
//                    </div>
//                </div>
//            </body>
//            </html>';
//    }
//}