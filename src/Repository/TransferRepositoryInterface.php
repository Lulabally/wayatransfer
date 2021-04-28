<?php

namespace App\Repository;

use App\Entity\Transfer;

interface TransferRepositoryInterface 
{

    public function save(Transfer $transfer);

}