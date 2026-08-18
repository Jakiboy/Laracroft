<?php

namespace Laracroft\Controllers;

use Laracroft\Helpers\Response;

class MainController extends Controller
{
    public function index() : Response
    {
        return $this->setResponse();
    }
}
