<?php

namespace Tritility\MatthewCSVTest\Imports;

use Illuminate\Support\Facades\Hash;

class ClientsImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return Client|null
     */
    public function model(array $row)
    {
        return new Client([
           'name'     => $row[0],
           'email'    => $row[1], 
           'password' => Hash::make($row[2]),
        ]);
    }
}
