
namespace <?=$this->namespace;?>\Command;

use Neptune\Command\Command;
use Neptune\Console\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

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
