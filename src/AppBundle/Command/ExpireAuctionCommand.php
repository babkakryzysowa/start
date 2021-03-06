<?php
namespace AppBundle\Command;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\Auction;

class ExpireAuctionCommand extends Command{
    /**
     *
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager){
        parent::__construct();
        $this->entityManager = $entityManager;
    }
/**
 * {@inheridoc}
 */
    protected function configure(){
        $this
        ->setName("app:expire_auction")
        ->setDescription("Komenda do wygaszania aukcji.");
    }

    public function execute(InputInterface $input, OutputInterface $output){
    //    $entityManager = $this->getContainer()->get("doctrine.orm.entity_manager");
        $auctions = $this->entityManager->getRepository(Auction::class)->findActiveExpired();
        $output->writeln(sprintf("Znaleziono <info>%d</info> aukcji do wygaszenia", count($auctions)));

        foreach( $auctions as $auction){
            $auction->setStatus(Auction::STATUS_FINSHED);
            $this->entityManager->persist($auction);
        }
        $this->entityManager->flush();
        $output->writeln("Udało się zaktualizować aukcje.");

    }
}

 ?>
