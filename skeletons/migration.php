
namespace <?=$this->namespace;?>\Migrations;

use Neptune\Database\Migration\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

<?=$this->class_info;?>
class <?=$this->class_name;?> extends AbstractMigration
{
    protected $description = '<?=$this->description;?>';

    public function up(Schema $schema)
    {
        $table = $schema->createTable('');

        $id = $table->addColumn('id', 'integer', ['unsigned' => true]);
        $id->setAutoIncrement(true);
        $table->setPrimaryKey(['id']);

        return $schema;
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('');
    }
}
