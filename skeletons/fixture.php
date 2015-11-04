
namespace <?=$this->namespace;?>\Fixtures;

use Doctrine\DBAL\Connection;
use ActiveDoctrine\Fixture\FixtureInterface;

<?=$this->class_info;?>
class <?=$this->class_name;?> implements FixtureInterface
{
    public function load(Connection $connection)
    {
    }

    public function getTables()
    {
        return [

        ];
    }
}
