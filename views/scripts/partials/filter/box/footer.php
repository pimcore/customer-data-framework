
        </div>
        <!-- /.box-body -->

        <div class="box-footer text-right">

            <a href="<?= $this->selfUrl($this->request, true, $this->addPerPageParam($this->request)) ?>"
               class="btn btn-default">
                <i class="fa fa-ban"></i>
                Clear Filters
            </a>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-filter"></i>
                Apply Filters
            </button>
        </div>
        <!-- /.box-footer -->

    </form>
</div>
