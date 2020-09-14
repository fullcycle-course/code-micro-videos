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
        httpVideo.get('cast-members').then(response => setData(response.data.data));
    }, []);
    return (
        <Page title="Listagem de membros do elenco">
            <Box dir={'rtl'}>
                <Fab
                    title=""
                    size="small"
                    component={Link}
                    to="/cast-members/create"
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