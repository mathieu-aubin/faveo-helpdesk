<?php

namespace App\Exceptions;

// controller
use Bugsnag;
//use Illuminate\Validation\ValidationException;
use Bugsnag\BugsnagLaravel\BugsnagExceptionHandler as ExceptionHandler;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
// use Symfony\Component\HttpKernel\Exception\HttpException;
// use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
//        'Symfony\Component\HttpKernel\Exception\HttpException',
//        'Illuminate\Http\Exception\HttpResponseException',
        ValidationException::class,
        AuthorizationException::class,
        HttpResponseException ::class,
        ModelNotFoundException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
    ];

    /**
     * Create a new controller instance.
     * constructor to check
     * 1. php mailer.
     *
     * @return void
     */
    // public function __construct(PhpMailController $PhpMailController)
    // {
    //     $this->PhpMailController = $PhpMailController;
    // }

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $e
     *
     * @return void
     */
    public function report(Exception $e)
    {
        $debug = \Config::get('app.bugsnag_reporting');
        $debug = ($debug) ? 'true' : 'false';
        if ($debug == 'false') {
            Bugsnag::setBeforeNotifyFunction(function ($error) {
                return false;
            });
        }

        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $e
     *
     * @return \Illuminate\Http\Response
     */
//    public function render($request, Exception $e) {
//        if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
//            return response()->json(['message' => $e->getMessage(), 'code' => $e->getStatusCode()]);
//        } elseif ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
//            return response()->json(['message' => $e->getMessage(), 'code' => $e->getStatusCode()]);
//        }
    // This is to check if the debug is true or false
//        if (config('app.debug') == false) {
//            // checking if the error is actually an error page or if its an system error page
//            if ($this->isHttpException($e) && $e->getStatusCode() == 404) {
//                return response()->view('errors.404', []);
//            } else {
//                // checking if the application is installed
//                if (\Config::get('database.install') == 1) {
//                    // checking if the error log send to Ladybirdweb is enabled or not
//                    if (\Config::get('app.ErrorLog') == '1') {
//
//                    }
//                }
//                return response()->view('errors.500', []);
//            }
//        }
    // returns non oops error message
//        return parent::render($request, $e);
    // checking if the error is related to http error i.e. page not found
//        if ($this->isHttpException($e)) {
//            // returns error for page not found
//            return $this->renderHttpException($e);
//        }
//        // checking if the config app sebug is enabled or not
//        if (config('app.debug')) {
//            // returns oops error page i.e. colour full error page
//            return $this->renderExceptionWithWhoops($e);
//        }
    //return parent::render($request, $e);
//    }

    /**
     * function to generate oops error page.
     *
     * @param \Exception $e
     *
     * @return \Illuminate\Http\Response
     */
//    protected function renderExceptionWithWhoops(Exception $e) {
//        // new instance of whoops class to display customized error page
//        $whoops = new \Whoops\Run();
//        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
//
//        return new \Illuminate\Http\Response(
//                $whoops->handleException($e), $e->getStatusCode(), $e->getHeaders()
//        );
//    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param type      $request
     * @param Exception $e
     *
     * @return type mixed
     */
    public function render($request, Exception $e)
    {
        switch ($e) {
            case $e instanceof \Illuminate\Http\Exception\HttpResponseException:
                return parent::render($request, $e);
            case $e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException:
                return response()->json(['message' => $e->getMessage(), 'code' => $e->getStatusCode()]);
            case $e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException:
                return response()->json(['message' => $e->getMessage(), 'code' => $e->getStatusCode()]);
            default:
                return $this->common($request, $e);
        }
    }

    /**
     * Function to render 500 error page.
     *
     * @param type $request
     * @param type $e
     *
     * @return type mixed
     */
    public function render500($request, $e)
    {
        if (config('app.debug') == true) {
            return parent::render($request, $e);
        }

        return redirect()->route('error500', []);
    }

    /**
     * Function to render 404 error page.
     *
     * @param type $request
     * @param type $e
     *
     * @return type mixed
     */
    public function render404($request, $e)
    {
        if (config('app.debug') == true) {
            return parent::render($request, $e);
        }

        return redirect()->route('error404', []);
    }

    /**
     * Common finction to render both types of codes.
     *
     * @param type $request
     * @param type $e
     *
     * @return type mixed
     */
    public function common($request, $e)
    {
        switch ($e) {
            case $e instanceof HttpException:
                return $this->render404($request, $e);
            case $e instanceof NotFoundHttpException:
                return $this->render404($request, $e);
            default:
                return $this->render500($request, $e);
        }

        return parent::render($request, $e);
    }
}
