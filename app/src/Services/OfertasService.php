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
        $this->atualizarOfertaVencedora($ofertaVencedora);
        $this->atualizarOfertasPerdedoras($ofertas);

        // AQUI PODEMOS ENVIAR AS NOTIFICAÇÕES POSTERIORMENTE
    }

    public function atualizarOfertaVencedora($ofertaVencedora)
    {
        //TODO IMPLEMENTAR UPDATE NA OFERTA VENCEDORA
    }

    public function atualizarOfertasPerdedoras($ofertas)
    {
        //TODO IMPLEMENTAR O UPDATE NAS OFERTAS DIFERENTES DA VENCEDORA
    }

}