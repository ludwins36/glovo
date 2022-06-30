<?php
namespace VexShipping\Glovo\Helper;

class Data
{

    protected $scopeConfig;
    
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }


    public function getDataconfig($name){
        return $this->scopeConfig->getValue('carriers/glovo/'.$name, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    
    public function getActive(){
        return $this->getDataconfig("active");
    }
    public function getNombre(){
        return $this->getDataconfig("name");
    }
    public function getTitulo(){
        return $this->getDataconfig("titulo");
    }
    public function getTipoPrecio(){
        return $this->getDataconfig("tipo_precio");
    }
    public function getPrecio(){
        return $this->getDataconfig("precio");
    }

    public function getEstadoPedido(){
        return $this->getDataconfig("glovo_w5/estado_pedido");
    }
    public function getCambiarEstado(){
        return $this->getDataconfig("glovo_w5/cambiar_estado");
    }
    public function getNuevoEstadoPedido(){
        return $this->getDataconfig("glovo_w5/nuevo_estado_pedido");
    }

    public function getNombreContacto(){
        return $this->getDataconfig("glovo_w1/nombre_contacto");
    }
    public function getTelefonoContacto(){
        return $this->getDataconfig("glovo_w1/nombre_telefono");
    }
    public function getDireccion(){
        return $this->getDataconfig("glovo_w1/direccion");
    }
    public function getReferencia(){
        return $this->getDataconfig("glovo_w1/referencia");
    }
    public function getLatitud(){
        return $this->getDataconfig("glovo_w1/latitud");
    }
    public function getLongitud(){
        return $this->getDataconfig("glovo_w1/longitud");
    }
    public function getCodigo(){
        return $this->getDataconfig("glovo_w1/codigo");
    }
    public function getFeriados(){
        return $this->getDataconfig("glovo_w1/commands_list");
    }
    public function getTrabajos(){
        return $this->getDataconfig("glovo_w1/commands_list_work");
    }

    public function getGoogleKey(){
        return $this->getDataconfig("glovo_w3/google_key");
    }

    public function getApiKey(){
        return $this->getDataconfig("glovo_w4/apiKey");
    }
    public function getApiSecret(){
        return $this->getDataconfig("glovo_w4/apiSecret");
    }
    public function getHorario(){
        return $this->getDataconfig("glovo_w4/horario");
    }
    public function getAmbiente(){
        return $this->getDataconfig("glovo_w4/ambiente");
    }

}