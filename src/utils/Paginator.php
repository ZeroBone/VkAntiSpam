<?php

namespace VkAntiSpam\Utils;

interface PaginatorClient {

    public function printPage($paginator, $offset);

    public function getPageUrl($pageNumber);

}

class Paginator {

    const NEAR_PAGES_COUNT = 3;

    private $totalItems;

    private $itemsPerPage;

    public $currentPage;

    private $c;

    /**
     * Paginator constructor.
     * @param $totalItems int
     * @param $itemsPerPage int
     * @param $currentPage int
     * @param $c PaginatorClient
     */
    public function __construct($totalItems, $itemsPerPage, $currentPage, $c) {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = $currentPage;
        $this->c = $c;
    }

    public function printContent() {

        $this->c->printPage($this, max($this->currentPage - 1, 0) * $this->itemsPerPage);

    }

    public function printPagination() {

        $totalPages = ceil($this->totalItems / $this->itemsPerPage);

        ?>
        <ul class="pagination">
            <?php if ($this->currentPage <= 1): ?>
                <li class="page-item page-prev disabled">
                    <a class="page-link" href="<?= $this->c->getPageUrl(1); ?>" tabindex="-1">
                        Пред.
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item page-prev">
                    <a class="page-link" href="<?= $this->c->getPageUrl($this->currentPage - 1); ?>">
                        Пред.
                    </a>
                </li>
            <?php endif; ?>
            <?php for ($pageNumber = max(1, $this->currentPage - static::NEAR_PAGES_COUNT + 1); $pageNumber <= min($totalPages, $this->currentPage + static::NEAR_PAGES_COUNT - 1); $pageNumber++): ?>
                <?php if ($pageNumber === $this->currentPage): ?>
                    <li class="page-item active">
                        <a class="page-link" href="<?= $this->c->getPageUrl($pageNumber); ?>"><?= number_format($pageNumber, 0, '.', ','); ?></a>
                    </li>
                <?php else: ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $this->c->getPageUrl($pageNumber); ?>"><?= number_format($pageNumber, 0, '.', ','); ?></a>
                    </li>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($this->currentPage >= $totalPages): ?>
                <li class="page-item page-next disabled">
                    <a class="page-link" href="<?= $this->c->getPageUrl($totalPages); ?>" tabindex="-1">
                        След.
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item page-next">
                    <a class="page-link" href="<?= $this->c->getPageUrl($this->currentPage + 1); ?>">
                        След.
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        <?php

    }

}