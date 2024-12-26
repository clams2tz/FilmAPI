<?php

namespace App\Controller;

use App\Entity\Films;
use App\Repository\FilmsRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 #[Route('/films')]

class FilmsController extends AbstractController
{
    private $filmsRepository;
    private $serializer;
    public function __construct(FilmsRepository $filmsRepository, SerializerInterface $serializer)
    {
        $this->filmsRepository = $filmsRepository;
        $this->serializer = $serializer;
    }
    #[Route('/', name: 'get_films', methods:['GET'])]
    public function getFilms(): JsonResponse
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $films = $this->filmsRepository->findAll();
        $data = $this->serializer->serialize($films, 'json', ['groups' => 'get_films']);
        // $data = array_map(function ($film) {
        //     return [
        //         'id' => $film->getId(),
        //         'title' => $film->getTitle(),
        //         'description' => $film->getDescription(),
        //         'release_date' => $film->getReleaseDate(),
        //         'director' => $film->getDirector(),
        //         // 'categories' => $film->getCategory()
        //     ];
        // }, $films);
        return new JsonResponse(                                      
            $data, status: 200, headers: [], json: true);
        
    }

    #[Route('/{id}', name: 'get_film', methods:['GET'])]
    public function getFilm(Films $film, SerializerInterface $serializer): JsonResponse
    {
        $film = $this->serializer->normalize($film, 'json', ['groups' => 'get_film']);
        return $this->json($film);
    }

    #[Route('/', name: 'add_film', methods:['POST'])]
    public function addFilm(Request $request, EntityManagaerInterface $em): JsonResponse
    {   
        $data = json_decode($request->getContent(), true);

        if(!isset($data['title']) || !isset($data['description']) || !isset($data['release_date']) || !isset($data['director'])) {
        return $this->json(['error' => 'Missing data'], status: 400);
        }
            $film = new Films();
            $film->setTitle($data['title']);
            $film->setDescription($data['description']);
            $film->setReleaseDate(new \DateTime($data['release_date']));
            $film->setDirector($data['director']);

            $em->persist($film);
            $em->flush();

            return $this->json([
                'message'=> 'Film added successfully'], status: 201);
            
    }

    #[Route('/{id}', name: 'edit_film', methods:['PUT'])]
    public function editFilm(Films $film, EntityManagaerInterface $em): JsonResponse
    {
        if(!$film) {
            return $this->json(['error' => 'Film not found'], status: 404);
        }

        $data = json_decode($film->getContent(), true);
        if(!isset($data['title'])) {
            $film->setTitle($data['title']);
        }  
        if(!isset($data['description'])) {
            $film->setDescription($data['description']);
        } 
        if(!isset($data['release_date'])) {
            $film->setReleaseDate(new \DateTime($data['release_date']));
        } 
        if(!isset($data['director'])) {
            $film->setDirector($data['director']);
        } 

        $em->persist($film);
        $em->flush();

        return $this->json(['message' => 'Film updated successfully'], status: 200);
    }

    #[Route('/{id}', name: 'delete_film', methods:['DELETE'])]
    public function deleteFilm(Films $film, EntityManagaerInterface $em): JsonResponse
    {
        if(!$film){
        return $this->json(['error' => 'Film not found!'], status: 404);
        }    

    $em->remove($film);
    $em->flush();
    return $this->json(['message' => 'Film deleted successfully'], status: 200);
    }
}
