<?php
declare(strict_types=1);

namespace App\Actions\UI;

use App\Actions\Action;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;

class UI_Home extends Action
{

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container )
    {
        parent::__construct($container );
    }

    protected function action() : Response {
        
        return $this->renderer->render( $this->response, 'index.phtml', [

        ]);
    }
}