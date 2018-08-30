<?php
namespace chencaicc\cccache;

interface ICache
{

	public function set($key,$val,$life_time=null);

	public function get($key);

	public function delete($key);

	public function deleteAll();

}