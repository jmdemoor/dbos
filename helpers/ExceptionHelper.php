<?php

namespace app\helpers;

use Yii;
use yii\web\Session;

class ExceptionHelper
{
    const CC_AUTH_ERROR = 'SPM035';

    /**
     * Standard handler for error level exceptions. Logs detailed error and adds Flash message to the current
     * session
     *
     * @param Session $session
     * @param $code
     * @param $message
     * @param array $errors
     * @param bool $show_friendly
     */
    public static function handleError(Session $session, $code, $message, array $errors, $show_friendly = false)
    {
        Yii::error("*** $code: $message. Error(s) " . print_r ($errors, true));
        if ($show_friendly)
            $msg = "$message. Code: `$code`";
        else
            $msg = "Problem with post or action. Please contact support.  Error: `$code`";
        $session->addFlash('error', $msg);
    }

}