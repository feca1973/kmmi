// The gallery pages: a masonry grid of the thumbnails and a lightbox for the big images.
import GLightbox from 'glightbox';
import 'glightbox/dist/css/glightbox.min.css';
import Masonry from 'masonry-layout';
import imagesLoaded from 'imagesloaded';

const grid = document.querySelector('.grid');

if (grid) {
    imagesLoaded(grid, () => {
        new Masonry(grid, { columnWidth: 345, itemSelector: '.grid-item', gutter: 10 });
    });
    GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true });
}
