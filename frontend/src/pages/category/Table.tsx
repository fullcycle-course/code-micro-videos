import React, {useEffect, useState} from 'react';
import MUIDataTable, {MUIDataTableColumn} from "mui-datatables";
import {httpVideo} from "../../util/http";
import {Chip} from "@material-ui/core";

const columnDefinition: MUIDataTableColumn[] = [
    {
        name: 'name',
        label: 'Nome'
    },
    {
        name: 'is_active',
        label: 'Ativo?',
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return value ? <Chip label="Sim" color="primary"  /> : <Chip label="Não" color="secondary" />;
            }
        }
    },
    {
        name: 'created_at',
        label: 'Criado em',
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return <span>{new Intl.DateTimeFormat('pt-br').format(new Date(value))}</span>
            }
        }
    }
]

const data = [
    {name: 'test1', is_active: true, created_at: "2019-12-12"},
    {name: 'test2', is_active: false, created_at: "2019-12-13"},
    {name: 'test3', is_active: true, created_at: "2019-12-14"},
    {name: 'test4', is_active: false, created_at: "2019-12-15"},
]

type Props = {

};
const Table = (props: Props) => {
    const [data, setData] = useState([]);
    // componentDidMount - será executado uma única vez.
    useEffect(() => {
        httpVideo.get('categories').then(response => setData(response.data.data));
    }, [])
    return (
        <div>
            <MUIDataTable
                title="Listagem de categorias"
                columns={columnDefinition}
                data={data}
            />
        </div>
    );
};


export default Table;