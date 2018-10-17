<?php
namespace AutoValue\Demo;

interface MyValueType
{
    public function equals($foo): bool;
}