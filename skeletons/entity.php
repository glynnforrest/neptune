
namespace <?=$this->namespace;?>\Entity;

use Neptune\Database\Entity\Entity;

<?=$this->class_info;?>
class <?=$this->entity_name;?> extends Entity
{

    protected static $table = '<?=$this->table;?>';
    protected static $fields = array(
        'id',
    );
    protected static $relations = array();

}
