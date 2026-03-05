<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Console\Helper\Table;

#[AsCommand(
    name: 'app:post-comment',
    description: 'Add a short description for your command',
)]
class PostCommentCommand extends Command
{
    public function __construct(private PostRepository $postRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('mode', InputArgument::REQUIRED, 'Argument description')
            ->addOption('postID', null, InputOption::VALUE_REQUIRED, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('mode');
        $option1 = $input->getOption('postID');

        $post = $this->postRepository->find($option1);

        if ($arg1 === 'filter') {
            $comments = $post->getComments();

            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq('isApproved', false));

            $unapprovedComments = $comments->matching($criteria);

            foreach ($unapprovedComments as $comment) {
                $comments->removeElement($comment);
            }
            $rows = [];
            foreach ($comments as $comment) {
                $rows[] = [
                    $comment->getId(),
                    $comment->isApproved(),
                    $comment->getLikes()
                ];
            }
            $io->title('Комментарии у поста с ID = ' . $option1);
            $table = new Table($output);
            $table->setHeaders(['ID', 'isApproved', 'Likes']);
            $table->setRows($rows);
            $table->render();
        }
        if ($arg1 === 'average') {
            $comments = $post->getComments();
            $totalLikes = 0;
            foreach ($comments as $comment) {
                $totalLikes += $comment->getLikes();
            }
            $average = $totalLikes / $comments->count();
            $io->success('Среднее кол-во лайков: ' . $average);
        }
        return Command::SUCCESS;
    }
}
