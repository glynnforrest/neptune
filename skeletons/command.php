
namespace <?=$this->namespace;?>\Command;

use Neptune\Command\Command;
use Neptune\Console\Console;

<?=$this->class_info;?>
class <?=$this->class_name;?> extends Command
{

    protected $name = '<?=$this->name?>';
    protected $description = '<?=$this->description;?>';

    public function go(Console $console)
    {
        $console->writeln('Hello from <?=$this->name;?>');
    }

}
