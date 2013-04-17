
namespace <?=$this->namespace;?>\Thing;

use Neptune\Database\Thing;
use Neptune\Database\SQLQuery;

<?=$this->class_info;?>
class <?=$this->thing_name;?> extends Thing {

	protected static $table = '<?=$this->table;?>';
	protected static $fields = array();
	protected static $rules = array();
	protected static $relations = array();

	public static function buildForm($action = null, $values = array(), $errors = array(), $method = 'POST') {
		$f = parent::buildForm($action, $values, $errors, $method);
        //customise the form here
		return $f;
	}

}
