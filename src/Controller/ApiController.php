<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route("/api/quote", name: "api-qoute")]
    public function number(): Response
    {
        $qoute = [
        "Im polish, try me russian bitch",
        "Nomnom betyder stoppa in i munnen",
        "BSK - Bilar Suger Kuk"
        ];
        $randomQoute = $qoute[array_rand($qoute)];

        $response = new JsonResponse(
            [
                "quote" => $randomQoute,
                "date" => date("Y-m-d H-i-s")
            ]
        );

        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api", name: "api")]
    public function api(): Response
    {
        return $this->render('api.html.twig');
    }

}
