<?php
    // Determine the input ids
    $id = array();
    $id['sort'] = 'sort-' . uniqid();
    $id['filter'] = 'filter-' . uniqid();
    $id['search'] = 'search-' . uniqid();
    $id['search-icon'] = 'search-icon-' . uniqid();
    $id['clear'] = 'clear-' . uniqid();
    $id['searcher-form'] = 'searcher-form-' . uniqid();
    // Get the Pagination named params
    $sortParams = null;
    if (isset($this->params['named']['sort']) && $this->params['named']['sort']
        && isset($this->params['named']['direction']) && $this->params['named']['direction']
    ) {
        $sortParams = "/sort:" . $this->params['named']['sort'] . "/direction:" . $this->params['named']['direction'];
    }
?>
<script type="text/javascript">
    var searcher = {};

    searcher.QUERY_FIELD_SEARCH = "search";
    searcher.QUERY_FIELD_FILTER = "filter";

    searcher.container = $('<?php echo $container; ?>');

    searcher.baseUri = "/<?php echo $controller; ?>/<?php echo $action; ?>";

    searcher.sort = "<?php echo ((isset($sortParams) && $sortParams) ? $sortParams : ""); ?>";

    searcher.search = "<?php echo ((isset($search) && $search) ? $search : ""); ?>";

    searcher.filter = "<?php echo ((isset($filter) && $filter) ? $filter : ""); ?>";

    searcher.before = function() {
        <?php echo ((isset($before) && $before) ? $before : ""); ?>
    }

    searcher.buildUri = function() {
        return searcher.baseUri
                + searcher.sort
                + "?" + searcher.QUERY_FIELD_SEARCH + "=" + searcher.search
                + "&" + searcher.QUERY_FIELD_FILTER + "=" + searcher.filter;
    }

    searcher.fetch = function(location) {
        if (typeof location !== 'undefined') {
            $.ajax({
                type: 'GET',
                url: location,
                beforeSend: function(jqXHR, settings) {
                    searcher.container.closest('.modal').find('.modal-dialog > .modal-content > .modal-header > .modal-loader').show();
                },
                complete: function(jqXHR, textStatus) {
                    searcher.container.closest('.modal').find('.modal-dialog > .modal-content > .modal-header > .modal-loader').hide();
                },
                success: function(data, textStatus, jqXHR) {
                    searcher.container.html(data);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    searcher.container.html("The list could not be retrieved");
                }
            });
        }
    }

    $(document).ready(function() {
        searcher.before();

        $('#<?php echo $id['sort']; ?>').on('change', function(e) {
            searcher.sort = $(this).val();
            searcher.fetch(searcher.buildUri());
        });
        
        $('#<?php echo $id['filter']; ?>').on('change', function(e) {
            searcher.filter = $(this).val();
            searcher.fetch(searcher.buildUri());
        });

        $('#<?php echo $id['search']; ?>').on('keypress', function(e) {
            if (e.keyCode === 13 || e.keyCode === 9) {
                searcher.search = encodeURIComponent($(this).val());
                searcher.fetch(searcher.buildUri());
            }
        });

        $('#<?php echo $id['search-icon']; ?>').on('click', function(e) {
            var val = $('#<?php echo $id['search']; ?>').val();
            if (typeof val !== 'undefined') {
                searcher.search = encodeURIComponent(val);
            }
            searcher.fetch(searcher.buildUri());
        });

        $('#<?php echo $id['clear']; ?>').on('click', function(e) {
            searcher.sort = searcher.search = searcher.filter = "";
            searcher.fetch(searcher.baseUri);
        });

        $('#<?php echo $id['searcher-form']; ?>').on('submit', function(e) {
            e.preventDefault();
        });
    });
</script>
<div class="searcher text-center">
    <form accept-charset="utf-8" id="<?php echo $id['searcher-form']; ?>" class="form-inline" role="form">
        <?php if (isset($hasSearch) && $hasSearch === true) : ?>
        <div class="form-group">
            <div class="input-group">
                <input type="text" id="<?php echo $id['search']; ?>" class="form-control" name="data[search]" value="<?php echo (isset($search) && $search) ? $search : "";?>" placeholder="Search" />
                <span id="<?php echo $id['search-icon']; ?>" class="input-group-btn">
                    <button type="button" class="btn btn-default">
                        <span class="glyphicon glyphicon-search"></span>
                    </button>
                </span>
            </div>
        </div>
        <?php endif; ?>
        <?php if (isset($sorts) && is_array($sorts) && !empty($sorts)) : ?>
        <div class="form-group">
            <select id="<?php echo $id['sort']; ?>" class="form-control" name="data[sort]">
                <option value="">Sort</option>
                <?php foreach ($sorts as $value => $display) : ?>
                <option value="<?php echo $value; ?>"<?php echo (isset($sortParams) && ($value == $sortParams)) ? " selected" : ""; ?>><?php echo $display; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <?php if (isset($filters) && is_array($filters) && !empty($filters)) : ?>
        <div class="form-group">
            <select id="<?php echo $id['filter']; ?>" class="form-control" name="data[filter]">
                <option value="">Filter</option>
                <?php foreach ($filters as $value => $display) : ?>
                    <option value="<?php echo $value; ?>"<?php echo (isset($filter) && ($value == $filter)) ? " selected" : ""; ?>><?php echo $display; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <button type="button" id="<?php echo $id['clear']; ?>" class="btn btn-default">
            <span class="glyphicon glyphicon-remove"></span> Clear
        </button>
    </form>
</div>