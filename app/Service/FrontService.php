<?php 

namespace App\Service;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use WorkshopRepositoryInterface;

class FrontService {
    protected $categoryRepository;
    protected $workshopRepository;

    public function __construct(WorkshopRepositoryInterface $workshopRepository, CategoryRepositoryInterface $categoryRepository) {
        $this->categoryRepository = $categoryRepository;
        $this->workshopRepository = $workshopRepository;
    }

    public function getFrontPageData() {
        $categories = $this->categoryRepository->getAllCategories();
        $newWorkshops = $this->workshopRepository->getAllNewworkshops();
        
        return compact('categories', 'newWorkshops'); 
    }
}