<?php

namespace app\modules\v1\controllers;

use Yii; // Added for Yii::$app access if needed in future
use yii\rest\Controller;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors; // Added for CORS

class ApiController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Remove CSRF validation - moved to init() for clarity as it's a property of request component

        // Content negotiation for JSON response format
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        // Add CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                // restrict access to
                //'Origin' => ['http://localhost:3000', 'https://myfrontend.com'], // TODO: Restrict in production
                'Origin' => ['*'], // Allow all origins for development
                // Allow only POST, GET, PUT, PATCH, DELETE, HEAD, OPTIONS methods
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                // Allow only headers 'X-Wsse'
                'Access-Control-Request-Headers' => ['*'], // Allow all headers for dev
                // Allow credentials (cookies, authorization headers, etc.) to be exposed to the browser
                'Access-Control-Allow-Credentials' => null, // Set to true if frontend needs to send cookies, false otherwise
                // Allow OPTIONS caching
                'Access-Control-Max-Age' => 86400, // One day
                // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page', 'X-Pagination-Page-Count', 'X-Pagination-Per-Page', 'X-Pagination-Total-Count', 'Link'],
            ],
        ];

        // Authenticator for all actions
        // Specific actions like 'login', 'register' will be made public using 'except' in child controllers.
        // The 'options' action is handled by the CORS filter if it's an OPTIONS request.
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            // 'except' is usually handled by child controllers to make specific actions public.
            // If an action is listed in 'except', it won't require authentication.
            // For example, if 'options' action is defined in every controller:
            // 'except' => ['options'],
        ];

        return $behaviors;
    }

    public function init()
    {
        parent::init();
        // Disable CSRF validation for API requests
        \Yii::$app->request->enableCsrfValidation = false;
    }

    /**
     * {@inheritdoc}
     * Centralized error handling could be added here if desired,
     * for example, by overriding afterAction to format errors consistently.
     */
}
