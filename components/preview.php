<?php

class ODE_CMP_Preview extends OW_Component
{
    public function __construct($component="data-sevc-controllet")
    {
        $this->assign("component", $component);
    }
}