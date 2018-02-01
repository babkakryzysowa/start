<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Auction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\AuctionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\FormError;
use AppBundle\Form\BidType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MyAuctionController extends Controller{
/**
 * @Route("/my", name="my_auction_index")
 * @return Response
 */
    public function index_auction(){
        $this->denyAccessUnlessGranted("ROLE_USER");
        $entityManager = $this->getDoctrine()->getManager();
        $auctions = $entityManager
            ->getRepository(Auction::class)
        //    ->findBy(["owner" => $this->getUser()]);
            ->findMyOrdered($this->getUser());

        return $this->render("MyAuction/index.html.twig",["auctions" => $auctions]);
    }
    /**
     * @Route("/my/auction/details/{id}", name="my_auction_details")
     * @param  Auction $auction
     * @return Response
     */
    public function detailsAction(Auction $auction){
        //Auction $auction sprawia, że poniższy fragemnt jest zbędny
        //$entityManager = $this->getDoctrine()->getManager();
        //$auction = $entityManager->getRepository(Auction::class)->findOneBy(["id" => $id]);
        $this->denyAccessUnlessGranted("ROLE_USER");

    if ($auction->getStatus() === Auction::STATUS_FINSHED){
        return $this->render("MyAuction/finished.html.twig", ["auction" => $auction]);
    }

        $deleteForm = $this->createFormBuilder()
        ->setAction($this->generateUrl("my_auction_delete", ["id" =>$auction->getId()]))
        ->setMethod(Request::METHOD_DELETE)
        ->add("submit",SubmitType::class,["label" => "Usun"])
        ->getForm();

        $finishForm = $this->createFormBuilder()
        ->setAction($this->generateUrl("my_auction_finish", ["id" =>$auction->getId()]))
        ->add("submit",SubmitType::class,["label" => "Zakoncz"])
        ->getForm();


        return $this->render(
            "MyAuction/details.html.twig",
            ["auction" => $auction,
            "deleteForm" => $deleteForm->createView(),
            "finishForm" => $finishForm->createView(),

        ]);

    }

    /**
     * @Route("/my/auction/add", name="my_auction_add")
     * @return Response
     */
    public function addAction(Request $request){

     $this->denyAccessUnlessGranted("ROLE_USER");

        $auction = new Auction();
        $form = $this->createForm(AuctionType::class, $auction);
     if ($request->isMethod("post")){
         $form->handleRequest($request);

         if($auction->getStartingPrice() >= $auction->getPrice() ){
             $form->get("startingPrice")->addError(new FormError("Cena wywoławcza nie może być wyższa od ceny kup teraz!"));
         }

         if($form->isValid()){

             $auction
                    ->setStatus(Auction::STATUS_ACTIVE)
                    ->setOwner($this->getUser());


             $entityManager = $this->getDoctrine()->getManager();
             $entityManager->persist($auction);
             $entityManager->flush();

            $this->addFlash("succes", "Aukcja: {$auction->getTitle()} została dodana.");
         //    $this->addFlash("error", "Aukcja: {$auction->getTitle()} nie została dodana.");

             return $this->redirectToRoute("my_auction_details", ["id" => $auction->getId()]);
         }
         $this->addFlash("error", "Aukcja: {$auction->getTitle()} nie została dodana.");

     }
        /*
        ->add("title", TextType::class)
        ->add("description", TextareaType::class)
        ->add("price", NumberType::class )*/
        return $this->render("MyAuction/add.html.twig",["form" => $form->createView()]);
    }


    /**
     * @Route("/my/auction/edit/{id}", name="my_auction_edit")
     * @param  Request $request
     * @param  Auction $auction
     * @return RedirectResponse|Response
     */
    public function editAuction(Request $request, Auction $auction) {
        $this->denyAccessUnlessGranted("ROLE_USER");

        if($this->getUser() !== $auction->getOwner()){
            throw new AccessDeniedException();

        }
        $form = $this->createForm(AuctionType::class,$auction);
        if($request->isMethod("post")){
            $form->handleRequest( $request);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($auction); // obiekt został zmieniony
            $entityManager->flush(); // zapisywanie do bazy
            $this->addFlash("succes", "Aukcja: {$auction->getTitle()} została poprawnie zedytowana.");

            return $this->redirectToRoute("my_auction_details", ["id" => $auction->getId()]);



        }
        return $this->render("MyAuction/edit.html.twig",["form" => $form->createView()]);
    }


    /**
     *
     * @Route("/my/auction/delete/{id}", name="my_auction_delete" , methods={"DELETE"})
     * @param  Auction $auction
     * @return RedirectResponse
     */
    public function deleteAuction(Auction $auction){
        $this->denyAccessUnlessGranted("ROLE_USER");

        if($this->getUser() !== $auction->getOwner()){
            throw new AccessDeniedException();

        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($auction);
        $entityManager->flush();
        $this->addFlash("succes", "Aukcja: {$auction->getTitle()} została usunięta.");

        return $this->redirectToRoute("my_auction_index");
    }

    /**
     * @Route("/my/auction/finish/{id}", name="my_auction_finish" ,methods={"POST"})
     * @param  Auction $auction
     * @return RedirectResponse
     */
    public function finishAuction(Auction $auction){
        $this->denyAccessUnlessGranted("ROLE_USER");

        if($this->getUser() !== $auction->getOwner()){
            throw new AccessDeniedException();

        }
        $auction
        ->setExpiresAt(new \DateTime())
        ->setStatus(Auction::STATUS_FINSHED);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($auction);
        $entityManager->flush();

        $this->addFlash("succes", "Aukcja: {$auction->getTitle()} została zakoczona.");

        return $this->redirectToRoute("my_auction_details", ["id" => $auction->getId()]);

    }




}

 ?>
