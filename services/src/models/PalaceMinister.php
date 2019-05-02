<?php  

namespace App\Model;
class PalaceMinister extends \Illuminate\Database\Eloquent\Model {  
  	protected $table = 'Palaces_Minister';
  	protected $primaryKey = 'id';
  	public $timestamps = false;

  	public function attachFiles()
    {
  		return $this->hasMany('App\Model\AttachFile', 'parent_id');
    }
    
}
