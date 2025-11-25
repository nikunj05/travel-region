<?php

namespace App\Interfaces;

interface BookingInterface
{
    public function index($request);

    public function show($order);

    public function store($request);

    public function applyCoupon($request);
}
