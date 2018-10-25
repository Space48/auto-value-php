<?php
namespace MyTemplates;

interface MyValueType
{
    public function equals($foo): bool;
}