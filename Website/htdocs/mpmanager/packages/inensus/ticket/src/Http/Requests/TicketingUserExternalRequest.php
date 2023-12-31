<?php
/**
 * Created by PhpStorm.
 * User: kemal
 * Date: 05.09.18
 * Time: 15:01
 */

namespace Inensus\Ticket\Http\Requests;


use App\Services\SessionService;
use Illuminate\Foundation\Http\FormRequest;

class TicketingUserExternalRequest extends FormRequest
{

    /**
     * Describes the rules which should be fulfilled by the request
     * @return array
     */
    public function rules(): array
    {
        return [
            'username' => 'required',
            'phone' => 'required',
        ];
    }

    public function getUserName():string
    {
        return $this->input('username');
    }

    public function getPhone():string
    {
        return (int)$this->input('phone');
    }
}
