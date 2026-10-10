<?php
// Stable, distinct fictional identities for each role. Never use names as account keys.
function demo_identity(string $role,int $n):string{
 $first=['Ayesha','Sadia','Farhana','Nadia','Samira','Nusrat','Maliha','Shaila','Rafia','Tania','Arif','Fahim','Tanvir','Imran','Rafi','Hasan','Nabil','Sajid','Rakib','Omar'];
 $last=['Rahman','Ahmed','Islam','Hasan','Chowdhury'];
 $roles=['tenant','owner','provider','agent','supervisor','finance','field_agent','admin'];
 $offset=array_search($role,$roles,true);if($offset===false)throw new InvalidArgumentException('Unknown demo role.');
 return $first[($n-1+$offset*3)%20].' '.$last[(intdiv($n-1,20)+$offset)%5];
}
