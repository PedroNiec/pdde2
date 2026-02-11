<?php

declare(strict_types=1);

class OfertasService
{
    public function __construct($repository)
    {
        $this->Repository = $repository;
    }

    public function atualizarOfertas($ofertaVencedora, $ofertas)
    {
        $this->handleOfertaVencedora($ofertaVencedora);
        $this->atualizarOfertasPerdedoras($ofertas, $ofertaVencedora);

        // AQUI PODEMOS ENVIAR AS NOTIFICAÇÕES POSTERIORMENTE
    }

    public function handleOfertaVencedora($ofertaVencedora)
    {
        $this->Repository->atualizarStatusOferta($ofertaVencedora, 'GANHA');
    }

    public function atualizarOfertasPerdedoras($ofertas, $ofertaVencedora)
    {
        foreach ($ofertas as $oferta){
            $id = $oferta['id'];

            if ($id !== $ofertaVencedora){
                $this->Repository->atualizarStatusOferta($id, 'PERDIDA');
            }

        }
    }

}