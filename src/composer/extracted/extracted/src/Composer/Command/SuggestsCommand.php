<?php











namespace Composer\Command;

use Composer\Repository\PlatformRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SuggestsCommand extends BaseCommand
{
protected function configure()
{
$this
->setName('suggests')
->setDescription('Show package suggestions.')
->setDefinition(array(
new InputOption('by-package', null, InputOption::VALUE_NONE, 'Groups output by suggesting package'),
new InputOption('by-suggestion', null, InputOption::VALUE_NONE, 'Groups output by suggested package'),
new InputOption('no-dev', null, InputOption::VALUE_NONE, 'Exclude suggestions from require-dev packages'),
new InputArgument('packages', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Packages that you want to list suggestions from.'),
))
->setHelp(<<<EOT

The <info>%command.name%</info> command shows a sorted list of suggested packages.

Enabling <info>-v</info> implies <info>--by-package --by-suggestion</info>, showing both lists.

EOT
)
;
}

protected function execute(InputInterface $input, OutputInterface $output)
{
$lock = $this->getComposer()->getLocker()->getLockData();

if (empty($lock)) {
throw new \RuntimeException('Lockfile seems to be empty?');
}

$packages = $lock['packages'];

if (!$input->getOption('no-dev')) {
$packages += $lock['packages-dev'];
}

$filter = $input->getArgument('packages');


 $installed = array();
foreach ($packages as $package) {
$installed[] = $package['name'];

if (!empty($package['provide'])) {
$installed = array_merge($installed, array_keys($package['provide']));
}

if (!empty($package['replace'])) {
$installed = array_merge($installed, array_keys($package['replace']));
}
}


 $installed = array_flip($installed);
ksort($installed);


 $platform = new PlatformRepository(array(), $this->getComposer()->getConfig()->get('platform') ?: array());


 $suggesters = array();
$suggested = array();
foreach ($packages as $package) {
$packageName = $package['name'];
if ((!empty($filter) && !in_array($packageName, $filter)) || empty($package['suggest'])) {
continue;
}
foreach ($package['suggest'] as $suggestion => $reason) {
if (false === strpos('/', $suggestion) && !is_null($platform->findPackage($suggestion, '*'))) {
continue;
}
if (!isset($installed[$suggestion])) {
$suggesters[$packageName][$suggestion] = $reason;
$suggested[$suggestion][$packageName] = $reason;
}
}
}
ksort($suggesters);
ksort($suggested);


 $mode = 0;
$io = $this->getIO();
if ($input->getOption('by-package') || $io->isVerbose()) {
$mode |= 1;
}
if ($input->getOption('by-suggestion')) {
$mode |= 2;
}


 if ($mode === 0) {
foreach (array_keys($suggested) as $suggestion) {
$io->write(sprintf('<info>%s</info>', $suggestion));
}

return;
}


 if ($mode & 1) {
foreach ($suggesters as $suggester => $suggestions) {
$io->write(sprintf('<comment>%s</comment> suggests:', $suggester));

foreach ($suggestions as $suggestion => $reason) {
$io->write(sprintf(' - <info>%s</info>: %s', $suggestion, $reason ?: '*'));
}
$io->write('');
}
}


 if ($mode & 2) {

 if ($mode & 1) {
$io->write(str_repeat('-', 78));
}
foreach ($suggested as $suggestion => $suggesters) {
$io->write(sprintf('<comment>%s</comment> is suggested by:', $suggestion));

foreach ($suggesters as $suggester => $reason) {
$io->write(sprintf(' - <info>%s</info>: %s', $suggester, $reason ?: '*'));
}
$io->write('');
}
}
}
}
