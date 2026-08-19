<?php

namespace Laracroft\Controllers;

use Laracroft\Helpers\Response;

class MainController extends Controller
{
    /**
     * @inheritdoc
     */
    public function index() : Response
    {
        return $this->setResponse();
    }
}
