<?php
namespace VexShipping\Glovo\Api;
 
interface ComercioInterface
{
    /**
     * Get customer questions
     *
     * @api
     * @param int $customerId
     * @return string questions
     */
    public function gettracking($customerId);
    /**
     * Get customer questions
     *
     * @api
     * @return string questions
     */
    public function getdataglovo();

    /**
     * Get customer questions
     *
     * @api
     * @return string questions
     */
    public function verificarPosicion();

    /**
     * Get customer questions
     *
     * @api
     * @return string questions
     */
    public function getverificarhora();
}