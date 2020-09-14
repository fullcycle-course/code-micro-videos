import * as React from 'react';
import {Page} from "../../components/Page";
import {Box, Fab} from "@material-ui/core";
import AddIcon from '@material-ui/icons/Add'
import {Link} from "react-router-dom";
import Table from "../../components/Table/Index";
import {useEffect, useState} from "react";
import {httpVideo} from "../../util/http";

import { columnDefinition} from './TableColumnDefinitions'

const List = () => {
    const [data, setData] = useState()
    useEffect(() => {
        httpVideo.get('genres').then(response => setData(response.data.data));
    }, []);
    return (
        <Page title="Listagem Categorias">
            <Box dir={'rtl'}>
                <Fab
                    title="Adicionar Categoria"
                    size="small"
                    component={Link}
                    to="/categories/create"
                >
                    <AddIcon/>
                </Fab>
            </Box>
            <Box>
                <Table
                    data={data}
                    columns={columnDefinition}
                />
            </Box>
        </Page>
    );
};

export default List;