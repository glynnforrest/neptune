
namespace <?=$this->namespace;?>\Entity;

use ActiveDoctrine\Entity\Entity;

<?=$this->class_info;?>
class <?=$this->entity_name;?> extends Entity
{

    protected static $table = '<?=$this->table;?>';
    protected static $primary_key = 'id';
    protected static $fields = [
        'id',
    ];
    protected static $relations = [

    ];

}
