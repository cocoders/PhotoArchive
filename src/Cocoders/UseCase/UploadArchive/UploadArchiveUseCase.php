<?php

namespace Cocoders\UseCase\UploadArchive;

use Cocoders\Archive\ArchiveFile;
use Cocoders\Archive\ArchiveRepository;
use Cocoders\Upload\UploadedArchive\UploadedArchiveFactory;
use Cocoders\Upload\UploadedArchive\UploadedArchiveRepository;
use Cocoders\Upload\UploadProvider\UploadProviderRegistry;
use Cocoders\UseCase\ResponderAware;
use Cocoders\UseCase\ResponderAwareBehavior;

class UploadArchiveUseCase implements ResponderAware
{
    use ResponderAwareBehavior;

    /**
     * @var \Cocoders\Upload\UploadedArchive\UploadedArchiveFactory
     */
    private $uploadedArchiveFactory;
    /**
     * @var \Cocoders\Upload\UploadProvider\UploadProviderRegistry
     */
    private $uploadProviderRegistry;
    /**
     * @var \Cocoders\Archive\ArchiveRepository
     */
    private $archiveRepository;
    /**
     * @var \Cocoders\Upload\UploadedArchive\UploadedArchiveRepository
     */
    private $uploadedArchiveRepository;

    public function __construct(
        UploadedArchiveFactory $uploadedArchiveFactory,
        UploadProviderRegistry $uploadProviderRegistry,
        ArchiveRepository $archiveRepository,
        UploadedArchiveRepository $uploadedArchiveRepository
    )
    {
        $this->uploadedArchiveFactory = $uploadedArchiveFactory;
        $this->uploadProviderRegistry = $uploadProviderRegistry;
        $this->archiveRepository = $archiveRepository;
        $this->uploadedArchiveRepository = $uploadedArchiveRepository;
    }

    public function execute(UploadArchiveRequest $request)
    {
        $archive = $this->archiveRepository->findByName($request->archiveName);
        if (!$archive) {
            $this->archiveNotFound($request->archiveName);

            return;
        }

        $archiveFilePaths = array_map(
            function (ArchiveFile $archiveFile) {
                return $archiveFile->getPath();
            },
            $archive->getFiles()
        );

        $providers = [];
        foreach ($request->providersNames as $providerName) {
            $provider = $this->uploadProviderRegistry->get($providerName);
            $provider->upload($request->archiveName, $archiveFilePaths);
            $providers[] = $provider;
        }

        $archive->upload();
        $uploadedArchive = $this->uploadedArchiveFactory->create($archive, $providers);
        $this->uploadedArchiveRepository->add($uploadedArchive);
        $this->archiveRepository->add($archive);

        $this->archiveUploaded($request->archiveName);
    }

    /**
     * @param string
     * @param string $archiveName
     */
    private function archiveNotFound($archiveName)
    {
        foreach ($this->responders as $responder) {
            /**
             * @var UploadArchiveResponder $responder
             */
            $responder->archiveNotFound($archiveName);
        }
    }

    /**
     * @param string
     * @param string $archiveName
     */
    private function archiveUploaded($archiveName)
    {
        foreach ($this->responders as $responder) {
            /**
             * @var UploadArchiveResponder $responder
             */
            $responder->archiveUploaded($archiveName);
        }
    }
}
