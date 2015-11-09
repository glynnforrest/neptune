
namespace <?=$this->namespace;?>\Command;

use Neptune\Command\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

<?=$this->class_info;?>
class <?=$this->class_name;?> extends Command
{
    protected $name = '<?=$this->name?>';
    protected $description = '<?=$this->description;?>';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hello from <?=$this->name;?> command');
    }
}
