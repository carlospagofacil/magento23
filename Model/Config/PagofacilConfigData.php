<?php

declare(strict_types=1);

namespace Pagofacil\Pagofacildirect\Model\Config;

class PagofacilConfigData
{
    
    /*
     * Datos de configuración del módulo en backend
     */
    
    private $display_user_id;
    
    private $display_user_branch_office_id;
    
    private $display_user_phase_id;
    
    private $endpoint_sandbox;
    
    private $endpoint_production;
    
    private $is_sandbox;
    
    private $monthy_installment_enabled;
    
    private $cctypes;

    private $three_ds_url = '/Woocommerce3ds/Form?';
    
    private $transaction_url = '/Wsrtransaccion/index/format/json?';
    
    private $verification_url = '/Wsrtransaccion/index/format/json?';
    
    const TRANSACTION_METHOD = 'transaccion';
    
    const VERIFICATION_METHOD = 'verificar';

    public function getTransactionUrl() {
        return $this->transaction_url;
    }
    
    public function getVerificationUrl() {
        return $this->verification_url;
    }
    
    public function getThreeDsEndPointUrl() {
        return $this->three_ds_url;
    }
    
    public function getTransactionEndPointUrl() {
        
        if($this->getIsSandbox()){
            return $this->endpoint_sandbox . $this->transaction_url;
        }
        
        return $this->endpoint_production . $this->transaction_url;
    }
    
    public function getVerificationEndPointUrl() {
        
        if($this->getIsSandbox()){
            return $this->endpoint_sandbox . $this->verification_url;
        }
        
        return $this->endpoint_production . $this->verification_url;
    }
    
    public function setUserId($display_user_id) {
        $this->display_user_id = $display_user_id;
    }

    public function getUserId() {
        return $this->display_user_id;
    }
    
    public function setBranchOfficeId($display_user_branch_office_id) {
        $this->display_user_branch_office_id = $display_user_branch_office_id;
    }

    public function getBranchOfficeId() {
        return $this->display_user_branch_office_id;
    }
    
    public function setPhaseId($display_user_phase_id) {
        $this->display_user_phase_id = $display_user_phase_id;
    }

    public function getPhaseId() {
        return $this->display_user_phase_id;
    }
    
    public function setEndpointSandbox($endpoint_sandbox) {
        $this->endpoint_sandbox = $endpoint_sandbox;
    }
    
    public function getEndpointSandbox() {
        return $this->endpoint_sandbox;
    }

    public function setEndpointProduction($endpoint_production) {
        $this->endpoint_production = $endpoint_production;
    }
    
    public function getEndpointProduction() {
        return $this->endpoint_production;
    }

    public function setIsSandbox($is_sandbox) {
        $this->is_sandbox = $is_sandbox;
    }

    public function getIsSandbox() {
        return $this->is_sandbox;
    }
    
    public function setMonthyInstallmentEnabled($monthy_installment_enabled) {
        $this->monthy_installment_enabled = $monthy_installment_enabled;
    }

    public function getMonthyInstallmentEnabled() {
        return $this->monthy_installment_enabled;
    }
    
    public function setCctypes($cctypes) {
        $this->cctypes = $cctypes;
    }
    
    public function getCctypes() {
        return $this->cctypes;
    }
    
    public function getEmpty3dsTransaction(){
        
        $transaccion_structure = [
                'method' => '',
                'idUsuario' => '',
                'idSucursal' => '',
                'idPedido' => '',
                'idServicio' => '',
                'Source' => '',
                'monto' => '',
                'plan' => '',
                'mensualidades' => '',
                'numeroTarjeta' => '',
                'cvt' => '',
                'mesExpiracion' => '',
                'anyoExpiracion' => '',
                'nombre' => '',
                'apellidos' => '',
                'cp' => '',
                'email' => '',
                'telefono' => '',
                'celular' => '',
                'calleyNumero' => '',
                'colonia' => '',
                'municipio' => '',
                'pais' => '',
                'estado' => ''
        ];
        

        return $transaccion_structure;
    }
    
}