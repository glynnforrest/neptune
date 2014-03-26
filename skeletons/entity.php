
namespace <?=$this->namespace;?>\Entity;

use Neptune\Database\Entity\Entity;
use Neptune\Database\SQLQuery;

<?=$this->class_info;?>
class <?=$this->entity_name;?> extends Entity {

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
