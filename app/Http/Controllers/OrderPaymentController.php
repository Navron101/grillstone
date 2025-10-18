<?php


namespace App\Http\Controllers;


use App\Services\PosService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;


class OrderPaymentController extends BaseController
{
public function __construct(private PosService $pos)
{
}


public function pay($orderId, Request $request)
{
$data = $request->validate([
'method' => 'required|in:cash,card,transfer,wallet',
'amount_cents' => 'required|integer|min:1',
'reference' => 'nullable|string|max:100',
]);


$userId = optional($request->user())->id;
$result = $this->pos->payOrder((int) $orderId, $data + ['user_id' => $userId]);


return response()->json($result);
}
}