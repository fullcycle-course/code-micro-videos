import {MUIDataTableColumn} from "mui-datatables";
import {Chip} from "@material-ui/core";
import React from "react";

export const columnDefinition: MUIDataTableColumn[] = [
    {
        name: 'name',
        label: 'Nome'
    },
    {
        name: 'categories',
        label: 'Categorias',
        options: {
            customBodyRender(value, tableMeta, updateValue){
                return value.map((category: {name: string}) => (<Chip label={category.name}  variant="outlined" />));
            }
        }
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