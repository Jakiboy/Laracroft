<?php

namespace Laracroft\Controllers;

use Laracroft\Helpers\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use \Throwable;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * @inheritdoc
     */
    protected function setResponse(string $message = '', $data = [], ?string $status = null, int $code = 200) : Response
    {
        return Response::set($message, $data, $status, $code);
    }

    /**
     * @inheritdoc
     */
    protected function success(string $message = 'Success', $data = [], string $status = 'success', int $code = 200) : Response
    {
        return $this->setResponse($message, $data, $status, $code);
    }

    /**
     * @inheritdoc
     */
    protected function error(string $message = 'Error', $data = [], string $status = 'error', int $code = 400) : Response
    {
        return $this->setResponse($message, $data, $status, $code);
    }

    /**
     * @inheritdoc
     */
    protected function exception(Throwable $exception) : Response
    {
        $status = method_exists($exception, 'getStatus') ? (int)$exception->getStatus() : 500;
        return $this->error(message: $exception->getMessage(), code: $status);
    }
}
